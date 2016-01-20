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
    }

    public function verifyConfig()
    {
        $config = $this->config();
        $table = $this->_table();

        if (!in_array($config['typeField'], $table->schema()->columns())) {
            throw new \Exception();
        }

        if (!$config['table']) {
            $this->config('table', $table->table());
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
        if (!$query->hydrate()) {
            return;
        }

        $assocs = $this->_table()->associations()->keys();

        $mapper = function ($row) use ($assocs) {
            foreach ($assocs as $k => $assoc) {
                unset($assocs[$k]);
                if ($row->has($assoc)) {
                    $assocs[$assoc] = $row->$assoc;
                    $row->unsetProperty($assoc);
                }
            }

            $type = $row[$this->config('typeField')];
            $entityClass = $this->_typeMap[$type]['entityClass'];
            $entity = new $entityClass($row->toArray(), [
                'markNew' => $row->isNew(),
                'markClean' => true,
                'guard' => false,
                'source' => $this->_typeMap[$type]['alias'],
            ]);
            $entity->virtualProperties($row->virtualProperties());

            foreach ($assocs as $assoc => $val) {
                $entity->set($assoc, $val);
                $entity->dirty($assoc, false);
            }

            return $entity;
        };

        $query->formatResults(function ($results) use ($mapper) {
            return $results->map($mapper);
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

        $entity->set($this->config('typeField'), $types[$class]);
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
        $connection = $this->_table->connection();
        $table = $this->config('table');
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
        $this->config('implementedMethods.' . $method, $method);
    }
}
