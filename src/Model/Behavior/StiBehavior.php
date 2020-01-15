<?php
declare(strict_types=1);

namespace Muffin\Sti\Model\Behavior;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Cake\Validation\Validator;

class StiBehavior extends Behavior
{
    protected $_defaultConfig = [
        'table' => null,
        'typeField' => 'type',
        'typeMap' => [],
    ];

    protected $_typeMap = [];

    /**
     * Initialized the Sti Behavior
     *
     * @param array $config Configuration options passed to the constructor
     * @return void
     *
     * @throws \Exception If the Entity isn't using the trait \Muffin\Sti\Model\Entity\StiAwareTrait
     */
    public function initialize(array $config): void
    {
        $this->verifyConfig();

        $defaultEntityClass = $this->_table()->getEntityClass();
        if ($defaultEntityClass === 'Cake\ORM\Entity') {
            $defaultEntityClass = current(Hash::extract($this->_typeMap, '{s}.entityClass'));
            $this->_table()->setEntityClass($defaultEntityClass);
        }

        if (!method_exists($defaultEntityClass, 'forCopy')) {
            throw new \Exception($defaultEntityClass . ' is not using the StiAwareTrait');
        }
    }

    /**
     * Verifies the configuration of the Behavior
     *
     * @return void
     *
     * @throws \Exception
     */
    public function verifyConfig(): void
    {
        $config = $this->getConfig();
        $table = $this->_table();

        if (!in_array($config['typeField'], $table->getSchema()->columns())) {
            throw new \Exception();
        }

        if (!$config['table']) {
            $this->getConfig('table', $table->getTable());
        }

        foreach ($config['typeMap'] as $key => $entityClass) {
            $this->addType($key, $entityClass);
        }

        parent::verifyConfig();
    }

    /**
     * Implementes magic methods for `newXXX`
     *
     * @param string $name Method name being called
     * @param array $args arguments passed to the original method
     *
     * @return mixed
     * @throws \Exception
     */
    public function __call($name, array $args)
    {
        $type = Inflector::underscore(substr($name, 3));

        if (!isset($args[0])) {
            $args[0] = [];
        }

        $args[0] += [$this->getConfig('typeField') => $type];

        return call_user_func_array([$this->_table($type), 'newEntity'], $args);
    }

    /**
     * Gets the real table
     *
     * @param string|null $key Table to find
     *
     * @return \Cake\ORM\Table
     *
     * @throws \Exception
     */
    protected function _table($key = null): Table
    {
        if ($key === null) {
            return $this->_table;
        }

        if (!isset($this->_typeMap[$key])) {
            throw new \Exception();
        }

        $options = $this->_typeMap[$key];
        $alias = $options['alias'];

        if (TableRegistry::getTableLocator()->exists($options['alias'])) {
            $options = [];
        }

        return TableRegistry::getTableLocator()->get($alias, $options);
    }

    /**
     * @param \Cake\Event\Event $event Event
     * @param \Cake\ORM\Query $query Quey
     * @param \ArrayObject $options Options
     * @param bool $primary If primary
     *
     * @return void
     */
    public function beforeFind(Event $event, Query $query, ArrayObject $options, $primary): void
    {
        if (!$query->isHydrationEnabled()) {
            return;
        }

        $query->formatResults(function ($results) {
            return $results->map(function ($row) {
                $type = $row[$this->getConfig('typeField')];
                $entityClass = $this->_typeMap[$type]['entityClass'];

                return new $entityClass($row->forCopy(), [
                    'markNew' => $row->isNew(),
                    'markClean' => true,
                    'guard' => false,
                    'source' => $this->_typeMap[$type]['alias'],
                ]);
            });
        });
    }

    /**
     * @param \Cake\Event\Event $event Event
     * @param \Cake\Validation\Validator $validator Original Validator
     * @param string $name Name of validator that is being built
     *
     * @return void
     *
     * @throws \Exception
     */
    public function buildValidator(Event $event, Validator $validator, $name): void
    {
        if ($name !== 'default') {
            return;
        }

        $class = $event->getSubject()->getEntityClass();

        $types = array_combine(
            Hash::extract($this->_typeMap, '{s}.entityClass'),
            array_keys($this->_typeMap)
        );

        $method = 'validation' . Inflector::classify($types[$class]);
        if (method_exists($this->_table(), $method)) {
            $this->_table()->{$method}($validator);
        }

        $table = $this->_table($types[$class]);
        if (method_exists($table, 'validationDefault')) {
            $table->validationDefault($validator);
        }
    }

    /**
     * @param \Cake\Event\Event $event Event
     * @param \Cake\Datasource\EntityInterface $entity Entity to save
     * @param \ArrayObject $options Options
     *
     * @return void
     *
     * @throws \Exception
     */
    public function beforeSave(Event $event, EntityInterface $entity, ArrayObject $options): void
    {
        $class = get_class($entity);
        $types = array_combine(
            Hash::extract($this->_typeMap, '{s}.entityClass'),
            array_keys($this->_typeMap)
        );

        if (!array_key_exists($class, $types)) {
            throw new \Exception();
        }

        $entity->set($this->getConfig('typeField'), $types[$class]);
    }

    /**
     * @param \Cake\Event\Event $event Event
     * @param \ArrayObject $data Data to marshall
     * @param \ArrayObject $options Options
     *
     * @return void
     *
     * @throws \Exception
     */
    public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options): void
    {
        $field = $this->getConfig('typeField');
        if (empty($data[$field])) {
            return;
        }

        if (!array_key_exists($data[$field], $this->_typeMap)) {
            throw new \Exception();
        }

        $this->_table()->setEntityClass($this->_typeMap[$data[$field]]['entityClass']);
    }

    /**
     * Adds the type and implements magic newXxx method
     * @param string $key Key
     * @param string $entityClass Entity to add
     *
     * @return void
     *
     * @throws \Exception
     */
    public function addType($key, $entityClass): void
    {
        [$namespace, $entityName] = explode('\\Entity\\', $entityClass);
        $connection = $this->_table->getConnection();
        $table = $this->getConfig('table');
        $alias = Inflector::pluralize($entityName);
        $className = $namespace . '\\Table\\' . $alias . 'Table';
        if (!class_exists($className)) {
            $className = null;
        }

        if (TableRegistry::getTableLocator()->exists($alias)) {
            $existingTable = TableRegistry::getTableLocator()->get($alias);
            if (
                $table !== $existingTable->getTable()
                || $connection !== $existingTable->getConnection()
                || $entityClass !== $existingTable->getEntityClass()
            ) {
                throw new \Exception();
            }
        }

        $this->_typeMap[$key] = compact('alias', 'entityClass', 'table', 'connection', 'className');

        $method = 'new' . Inflector::classify($entityName);
        $this->setConfig('implementedMethods.' . $method, $method);
    }
}
