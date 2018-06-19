<?php
namespace Muffin\Sti\Model\Behavior;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Cake\Validation\Validator;

class StiBehavior extends Behavior
{
    protected $_defaultConfig = [
        'table' => null,
        'typeField' => 'type',
        'typeMap' => []
    ];

    protected $_typeMap = [];

    public function initialize(array $config)
    {
        $this->verifyConfig();

        $defaultEntityClass = $this->_table()->getEntityClass();
        if ($defaultEntityClass === '\Cake\ORM\Entity') {
            $defaultEntityClass = current(Hash::extract($this->_typeMap, '{s}.entityClass'));
            $this->_table()->entityClass($defaultEntityClass);
        }

        if (!method_exists($defaultEntityClass, 'forCopy')) {
            throw new \Exception();
        }
    }

    public function verifyConfig()
    {
        $config = $this->getConfig();
        $table = $this->_table();

        if (!in_array($config['typeField'], $table->getSchema()->columns())) {
            throw new \Exception();
        }

        if (!$config['table']) {
            $this->setConfig('table', $table->getTable());
        }

        foreach ($config['typeMap'] as $key => $entityClass) {
            $this->addType($key, $entityClass);
        }

        parent::verifyConfig();
    }

    public function __call($method, $args)
    {
        $type = Inflector::underscore(substr($method, 3));

        if (!isset($args[0])) {
            $args[0] = [];
        }

        $args[0] += [$this->config('typeField') => $type];
        return call_user_func_array([$this->_table($type), 'newEntity'], $args);
    }

    protected function _table($key = null)
    {
        if ($key === null) {
            return $this->_table;
        }

        if (!isset($this->_typeMap[$key])) {
            throw new \Exception();
        }

        $options = $this->_typeMap[$key];
        $alias = $options['alias'];

        if (TableRegistry::exists($options['alias'])) {
            $options = [];
        }

        return TableRegistry::get($alias, $options);
    }

    public function beforeFind(Event $event, Query $query, ArrayObject $options, $primary)
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

    public function buildValidator(Event $event, Validator $validator, $name)
    {
        if ($name !== 'default') {
            return;
        }

        $class = $event->subject()->entityClass();

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

    public function beforeSave(Event $event, EntityInterface $entity, ArrayObject $options)
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

    public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
    {
        $field = $this->config('typeField');
        if (empty($data[$field])) {
            return;
        }

        if (!array_key_exists($data[$field], $this->_typeMap)) {
            throw new \Exception();
        }

        $this->_table()->entityClass($this->_typeMap[$data[$field]]['entityClass']);
    }

    public function addType($key, $entityClass)
    {
        list($namespace, $entityName) = explode('\\Entity\\', $entityClass);
        $connection = $this->_table->getConnection();
        $table = $this->getConfig('table');
        $alias = Inflector::pluralize($entityName);
        $className = $namespace . '\\Table\\' . $alias . 'Table';
        if (!class_exists($className)) {
            $className = null;
        }

        if (TableRegistry::exists($alias)) {
            $existingTable = TableRegistry::get($alias);
            if ($table !== $existingTable->table()
                || $connection !== $existingTable->connection()
                || $entityClass !== $existingTable->entityClass()
            ) {
                throw new \Exception();
            }

        }

        $this->_typeMap[$key] = compact('alias', 'entityClass', 'table', 'connection', 'className');

        $method = 'new' . Inflector::classify($entityName);
        $this->setConfig('implementedMethods.' . $method, $method);
    }
}
