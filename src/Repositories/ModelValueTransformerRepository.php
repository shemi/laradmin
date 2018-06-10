<?php

namespace Shemi\Laradmin\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Shemi\Laradmin\Contracts\HasMediaContract;
use Shemi\Laradmin\Models\Field;
use Spatie\MediaLibrary\Media;

class ModelValueTransformerRepository
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

        if($field->is_password || in_array($key, $model->getHidden())) {
            return null;
        }

        if(! $model->exists || (! $model->offsetExists($key) && ! $field->is_media)) {
            return static::getDefaultValue($field);
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

    protected function asJson()
    {
        return $this->getJsonTransformer()
            ->transform($this->field, $this->asModelValue(), $this->model, $this->internal);
    }

    protected function asModelValue()
    {
        return $this->model->getAttribute($this->modelKey);
    }

    public static function getDefaultValue(Field $field)
    {
        if($field->default_value !== null) {
            return $field->default_value;
        }

        if($field->nullable) {
            return null;
        }

        switch ($field->type) {
            case 'number':
            case 'text':
            case 'text_area':
            case 'date':
            case 'datetime':
                return "";

            case 'switch':
            case 'checkbox':
                return false;

            case 'select':
            case 'radio':
            case 'image':
            case 'file':
                return null;

            case 'object':
            case 'group':
                $object = [];

                if($field->is_support_sub_fields) {
                    /** @var Field $subField */
                    foreach ($field->getSubFields() as $subField) {
                        $object[$subField->key] = $subField->getDefaultValue();
                    }
                }

                return $object;

            case 'select_multiple':
            case 'checkboxes':
            case 'repeater':
            case 'files':
                return (array) [];

            default:
                return null;
        }

    }

    /**
     * @return MediaValueTransformerRepository
     */
    protected function getMediaTransformer()
    {
        return new MediaValueTransformerRepository();
    }

    /**
     * @return RelationshipValueTransformerRepository
     */
    protected function getRelationshipTransformer()
    {
        return new RelationshipValueTransformerRepository();
    }

    /**
     * @return JsonValueTransformerRepository
     */
    protected function getJsonTransformer()
    {
        return new JsonValueTransformerRepository();
    }

    public function fresh()
    {
        return new static;
    }

}