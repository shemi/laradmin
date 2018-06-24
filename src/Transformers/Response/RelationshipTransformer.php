<?php

namespace Shemi\Laradmin\Transformers\Response;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Shemi\Laradmin\Contracts\HasMediaContract;
use Shemi\Laradmin\Models\Field;
use Spatie\MediaLibrary\Media;

class RelationshipTransformer extends Transformer
{
    /**
     * @var Field $field
     */
    protected $field;

    /**
     * @var Model $model
     */
    protected $model;

    /**
     * @var string $modelKey
     */
    protected $modelKey;

    public function init(Field $field, $modelKey, Model $model, $internal = true)
    {
        $this->field = $field;
        $this->model = $model;
        $this->modelKey = $modelKey;

        return $this;
    }

    /**
     * @param Field $field
     * @param $modelKey
     * @param Model $model
     * @param bool $internal
     * @return array|Collection|mixed
     */
    public function transform(Field $field, $modelKey, Model $model, $internal = true)
    {
        $this->init($field, $modelKey, $model, $internal);

        $value = $this->model->getAttribute($this->modelKey);

        if(! $internal) {
            return $value;
        }

        if ($value instanceof Collection) {
            return $this->asRelationCollection($value);
        }

        if ($this->field->is_support_sub_fields && $value instanceof Model) {
            return $this->asSubModel($value);
        }

        if ($value instanceof Model) {
            return $value->getAttribute($this->field->getRelationKeyName($this->model));
        }

        return $value;
    }

    public function asRelationCollection(Collection $collection)
    {
        if(in_array($this->field->type, ['checkboxes'])) {
            return $collection->pluck($this->field->getRelationKeyName($this->model));
        }

        if($this->field->is_support_sub_fields) {
            return $collection->transform(function($model) {
                return $this->asSubModel($model);
            });
        }

        return $collection->transform(function($model) {
            return $this->asRelationModel($model);
        });
    }

    public function asSubModel(Model $model)
    {
        $return = [
            $model->getKeyName() => $model->getKey()
        ];

        $subFields = $this->field->getSubFields();

        if($subFields->isEmpty()) {
            return $return;
        }

        /** @var Field $field */
        foreach ($subFields as $field) {
            $return[$field->key] = $field->getModelValue($model);
        }

        return $return;
    }

    /**
     * @param Model $model
     * @return array
     * @throws \Shemi\Laradmin\Exceptions\ManagerDoesNotExistsException
     */
    public function asRelationModel(Model $model)
    {
        $labels = $this->field->relation_labels;
        $labelKey = array_shift($labels);

        $return = [
            'key' => $model->getAttribute($this->field->relation_key),
            'label' => $model->getAttribute($labelKey)
        ];

        if(! empty($labels)) {
            $return['extra_labels'] = [];

            foreach ($labels as $label) {
                $return['extra_labels'][$label] = $model->getAttribute($label);
            }
        }

        if($this->field->relation_image && $model instanceof HasMediaContract) {
            $media = $model->getMedia($this->field->relation_image['collection'])
                ->first();

            if($media) {
                $return['image'] = laradmin('links')
                    ->serveMedia(
                        $media->id,
                        $media->name,
                        $this->field->relation_image_conversion
                    );
            }
        }

        if($this->field->has_relationship_type) {
            $return['edit_link'] = laradmin('links')
                ->edit($this->field->relationship_type, $model);
        }

        return $return;
    }

}