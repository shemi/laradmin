<?php

namespace Shemi\Laradmin\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Shemi\Laradmin\Contracts\Repositories\TypeModelQueryRepository as TypeModelQueryRepositoryContract;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;

class TypeModelQueryRepository implements TypeModelQueryRepositoryContract
{
    /**
     * @var Type $type
     */
    protected $type;

    /**
     * @var Collection $fields
     */
    protected $fields;

    /**
     * @var \Illuminate\Database\Eloquent\Model $model
     */
    protected $model;

    /**
     * @param $id
     * @param Type $type
     * @param Collection $fields
     * @return \Illuminate\Database\Eloquent\Collection|Model
     */
    public function find($id, Type $type, Collection $fields = null)
    {
        $this->setModel(app($type->model));
        $this->setFields($fields ?: $type->fields);
        $this->setType($type);

        $query = $this->model->newQueryWithoutScopes();

        $this->load($query);

        return $query->findOrFail($id);
    }

    protected function load(&$query)
    {
        $columns = [];

        /** @var Field $field */
        foreach ($this->getRelationshipFields() as $field) {
            $columns[$field->key] = function($query) use ($field) {
                if($field->relation_order_key) {
                    $query->orderBy($field->relation_order_key);
                }
            };

        }

        $this->fields->first(function (Field $field) use ($columns) {
            if ($field->is_media) {
                $columns['media'] = function($query){};

                return true;
            }

            return false;
        });

        if(count(array_keys($columns)) > 0) {
            $query->with($columns);
        }

        return $this;
    }

    protected function getRelationshipFields()
    {
        $fields = $this->fields->reject(function(Field $field) {
            return ! $field->is_relationship || ! $field->getRelationClass($this->model);
        });

        return $fields;
    }

    /**
     * @param Type $type
     * @return TypeModelQueryRepository
     */
    public function setType(Type $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param Collection $fields
     * @return TypeModelQueryRepository
     */
    public function setFields(Collection $fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * @param Model $model
     *
     * @return TypeModelQueryRepository
     */
    public function setModel(Model $model)
    {
        $this->model = $model;

        return $this;
    }

    public function fresh()
    {
        return new static;
    }

}
