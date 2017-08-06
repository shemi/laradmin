<?php

namespace Shemi\Laradmin\Database\Schema;

use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\Table as DoctrineTable;
use ErrorException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

class Table extends DoctrineTable
{
    /**
     * @var Model
     */
    public $model;

    public $relations;

    public function __construct($tableName, $columns = array(), $indexes = array(), $fkConstraints = array(), $idGeneratorType = 0, array $options = array())
    {
        parent::__construct($tableName, $columns, $indexes, $fkConstraints, $idGeneratorType, $options);

        $this->setModel();
    }

    protected function setModel()
    {
        $namespace = '\\' . trim(config('laradmin.models.namespace'), '\\') . '\\';
        $className = $namespace.studly_case(str_singular($this->_name));

        if(class_exists($className) && $this->model = new $className instanceof Model) {
            $this->model = new $className;
            $this->relations = $this->getAllModelRelations();
        }
    }

    protected function getAllModelRelations()
    {
        $model = $this->model;

        $relationships = [];

        foreach((new ReflectionClass($model))->getMethods(ReflectionMethod::IS_PUBLIC) as $method)
        {
            if ($method->class != get_class($model) ||
                !empty($method->getParameters()) ||
                $method->getName() == __FUNCTION__) {
                continue;
            }

            try {
                $return = $method->invoke($model);

                if ($return instanceof Relation) {
                    $relationships[$method->getName()] = [
                        'type' => (new ReflectionClass($return))->getShortName(),
                        'model' => (new ReflectionClass($return->getRelated()))->getName()
                    ];
                }
            } catch(ErrorException $e) {}
        }

        return $relationships;
    }

    public function diff(DoctrineTable $compareTable)
    {
        return (new Comparator())->diffTable($this, $compareTable);
    }

    public function diffOriginal()
    {
        return (new Comparator())->diffTable(SchemaManager::getDoctrineTable($this->_name), $this);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'name'           => $this->_name,
            'columns'        => $this->exportColumnsToArray(),
            'primaryKeyName' => $this->_primaryKeyName,
            'options'        => $this->_options,
        ];
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * @return array
     */
    public function exportColumnsToArray()
    {
        $exportedColumns = [];

        foreach ($this->getColumns() as $name => $column) {
            $exportedColumns[] = Column::toArray($column);
        }

        return $exportedColumns;
    }



    public function __get($property)
    {
        $getter = 'get'.ucfirst($property);

        if (!method_exists($this, $getter)) {
            throw new \Exception("Property {$property} doesn't exist or is unavailable");
        }

        return $this->$getter();
    }
}