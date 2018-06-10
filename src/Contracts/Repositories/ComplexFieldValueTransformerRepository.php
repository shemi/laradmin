<?php

namespace Shemi\Laradmin\Contracts\Repositories;


use Illuminate\Database\Eloquent\Model;
use Shemi\Laradmin\Exceptions\CreateUpdateRelationModelNotFoundException;
use Shemi\Laradmin\Exceptions\CreateUpdateTransformCantFindCopyFieldOrAttributeException;
use Shemi\Laradmin\Exceptions\SyncMedia\SyncMediaException;
use Shemi\Laradmin\Models\Field;

interface ComplexFieldValueTransformerRepository extends Repository
{

    /**
     * @param $value
     * @param Field $field
     * @param Model $model
     * @param null|string $modelKey
     * @return array
     * @throws CreateUpdateRelationModelNotFoundException
     * @throws CreateUpdateTransformCantFindCopyFieldOrAttributeException
     * @throws SyncMediaException
     */
    public function transform($value, Field $field, Model $model, $modelKey = null);

}