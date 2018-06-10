<?php

namespace Shemi\Laradmin\Repositories;


use Illuminate\Database\Eloquent\Model;
use Shemi\Laradmin\Contracts\Repositories\TransformTypeModelDataRepository as TransformTypeModelDataRepositoryContract;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;

class TransformTypeModelDataRepository implements TransformTypeModelDataRepositoryContract
{

    /**
     * @var Model $model
     */
    protected $model;

    /**
     * @var Type $type
     */
    protected $type;

    public function transform(Type $type, Model $model = null)
    {
        $this->type = $type;

        if(! $model) {
            $model = app($type->model);
        }

        $this->model = $model;

        return $this->getTransformedData();
    }

    protected function getTransformedData()
    {
        $data = [];

        /** @var Field $field */
        foreach ($this->type->fields as $field) {
            $data[$field->key] = $field->getModelValue($this->model);
        }

        if(! array_key_exists($this->model->getKeyName(), $data)) {
            $data[$this->model->getKeyName()] = $this->model->getKey();
        }

        return $data;
    }

    public function fresh()
    {
        return new static;
    }

}
