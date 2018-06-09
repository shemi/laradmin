<?php

namespace Shemi\Laradmin\Models\Traits;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use \Illuminate\Database\Eloquent\Model as EloquentModel;
use Shemi\Laradmin\Contracts\HasMediaContract;
use Shemi\Laradmin\Data\Model;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;

/**
 * Shemi\Laradmin\Models\Traits\FieldHasRelationshipAttributes
 *
 * @property int $id
 * @property array|null $relationship
 * @property boolean $is_relationship
 * @property boolean $has_relationship_type
 * @property string $relationship_type_slug
 * @property Type $relationship_type
 * @property array $relation_labels
 * @property string $relation_key
 * @property array $relation_image
 * @property boolean $is_ajax_powered_relationship
 * @property string $key
 * @property string $type
 */

trait InteractsWithRelationship
{

    public function getIsRelationshipAttribute()
    {
        if($this->relationship === true) {
            return true;
        }

        return $this->relationship &&
            is_array($this->relationship) &&
            ! empty($this->relationship);
    }

    public function getIsAjaxPoweredRelationshipAttribute()
    {
        return array_get($this->relationship, 'ajax_powered', false);
    }

    public function getRelationLabelsAttribute()
    {
        $labels = (array) array_get($this->relationship, 'label', null) ?: [];

        if(empty($labels) && $this->is_support_sub_fields) {
            /** @var Field $field */
            foreach ($this->getSubFields() as $field) {
                if($field->isVisibleOn('browse')) {
                    $labels[] = $field->key;
                }
            }
        }

        return $labels;
    }

    public function getRelationKeyAttribute()
    {
        return array_get($this->relationship, 'key');
    }

    public function getRelationKeyName(EloquentModel $model)
    {
        $model = $this->getRelationModelClass($model);

        return $this->relation_key ?: $model->getKeyName();
    }

    public function getRelationImageAttribute()
    {
        $image = array_get($this->relationship, 'image', false);

        if($image) {
            $image = explode('@', $image);

            $image = [
                'collection' => $image[0],
                'conversion' => isset($image[1]) ? $image[1] : ''
            ];
        }

        return $image;
    }

    public function getRelationshipTypeSlugAttribute()
    {
        return array_get($this->relationship, 'type', false);
    }

    public function getHasRelationshipTypeAttribute()
    {
        if(! $typeSlug = $this->relationship_type_slug) {
            return false;
        }

        $type = Type::whereSlug($typeSlug);

        return (boolean) $type;
    }

    public function getRelationshipTypeAttribute()
    {
        return Type::whereSlug($this->relationship_type_slug);
    }

    /**
     * @param EloquentModel $model
     * @return Relation|bool
     */
    public function getRelationClass(EloquentModel $model)
    {
        $key = camel_case($this->key);

        if(! $this->is_relationship || ! method_exists($model, $key)) {
            return false;
        }

        $relation = $model->{$key}();

        if(! ($relation instanceof Relation)) {
            return false;
        }

        return $relation;
    }

    public function getRelationModelClass(EloquentModel $model = null)
    {
        if(! $model && ($this->has_relationship_type || isset($this->relationship['model']))) {
            return app($this->has_relationship_type ? $this->relationship_type->model : $this->relationship['model']);
        } elseif (! $model) {
            return false;
        }

        if(! $relation = $this->getRelationClass($model)) {
            return false;
        }

        return $relation->getRelated();
    }

    protected function transformRelationCollection(Collection $collection)
    {
        if(in_array($this->type, ['checkboxes'])) {
            return $collection->pluck($this->relation_key);
        }

        if($this->getSubFields()->isNotEmpty()) {
            return $collection->transform(function($model) {
                return $this->transformSubModel($model);
            });
        }

        return $collection->transform(function($model) {
            return $this->transformRelationModel($model);
        });
    }

    public function transformRelationModel(EloquentModel $model)
    {
        $labels = $this->relation_labels;
        $labelKey = array_shift($labels);

        $return = [
            'key' => $model->getAttribute($this->relation_key),
            'label' => $model->getAttribute($labelKey)
        ];

        if(! empty($labels)) {
            $return['extra_labels'] = [];

            foreach ($labels as $label) {
                $return['extra_labels'][$label] = $model->getAttribute($label);
            }
        }

        if($this->relation_image && $model instanceof HasMediaContract) {
            $media = $model->getMedia($this->relation_image['collection'])
                ->first();

            if($media) {

                $return['image'] = route('laradmin.serve', [
                    'mediaId' => $media->id,
                    'fileName' => $media->name,
                    'pc' => $this->relation_image['conversion']
                ]);
            }
        }

        if($this->has_relationship_type) {
            $type = $this->relationship_type;

            $return['edit_link'] = route("laradmin.{$type->slug}.edit", [
                "{$type->slug}" => $model->id
            ]);
        }

        return $return;
    }

    public function transformSubModel(EloquentModel $model)
    {
        $return = [
            $model->getKeyName() => $model->getKey()
        ];

        if(! $this->getSubFields()) {
            return $return;
        }

        /** @var Field $field */
        foreach ($this->getSubFields() as $field) {
            $return[$field->key] = $field->getModelValue($model);
        }

        return $return;
    }

}