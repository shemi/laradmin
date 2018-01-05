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

        $relationshipFields = $this->getRelationshipFields();

        if($relationshipFields->isNotEmpty()) {
            $query->with($relationshipFields->pluck('key')->toArray());
        }

        return $query->findOrFail($id);
    }


    protected function getRelationshipFields()
    {
        return $this->fields->reject(function(Field $field) {
            return ! $field->is_relationship || ! $field->getRelationClass($this->model);
        });
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
}
