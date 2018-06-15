<?php

namespace Shemi\Laradmin\Transformers\Response;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Shemi\Laradmin\Contracts\HasMediaContract;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Transformers\FieldDefaultValueTransformer;
use Spatie\MediaLibrary\Media;

class ModelTransformer extends Transformer
{
    /**
     * @var Field $field
     */
    protected $field;

    /**
     * @var string $modelKey
     */
    protected $modelKey;

    /**
     * @var Model $model
     */
    protected $model;

    /**
     * @var boolean $internal
     */
    protected $internal;

    /**
     * @param Field $field
     * @param $key
     * @param Model $model
     * @param bool $internal
     * @return $this|array|Collection|mixed|null|Media|static
     * @throws \Exception
     */
    public function transform(Field $field, $key, Model $model, $internal = true)
    {
        $this->field = $field;
        $this->modelKey = $key;
        $this->model = $model;
        $this->internal = $internal;

        if($this->field->is_password || in_array($this->modelKey, $model->getHidden())) {
            return null;
        }

        if(! $model->exists || (! $model->offsetExists($this->modelKey) && ! $field->is_media)) {
            return FieldDefaultValueTransformer::transform($field);
        }

        if($this->field->is_relationship) {
            return $this->asRelationship();
        }

        if($this->field->is_media) {
            return $this->asMedia();
        }

        if($this->field->is_support_sub_fields) {
            return $this->asJson();
        }

        return $this->asModelValue();
    }

    protected function asRelationship()
    {
        return $this->getRelationshipTransformer()
            ->transform($this->field, $this->modelKey, $this->model, $this->internal);
    }

    /**
     * @return array|Collection|null|Media
     * @throws \Exception
     * @throws \Shemi\Laradmin\Exceptions\ManagerDoesNotExistsException
     */
    protected  function asMedia()
    {
        return $this->getMediaTransformer()
            ->transform($this->field, $this->model, $this->internal);
    }

    /**
     * @return mixed
     * @throws \Exception
     * @throws \Shemi\Laradmin\Exceptions\ManagerDoesNotExistsException
     */
    protected function asJson()
    {
        return $this->getJsonTransformer()
            ->transform($this->field, $this->asModelValue(), $this->model, $this->internal);
    }

    protected function asModelValue()
    {
        return $this->model->getAttribute($this->modelKey);
    }

}