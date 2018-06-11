<?php

namespace Shemi\Laradmin\Models\Traits;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use \Illuminate\Database\Eloquent\Model as EloquentModel;
use Shemi\Laradmin\Contracts\HasMediaContract;
use Shemi\Laradmin\Data\Model;
use \Laradmin;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;
use Shemi\Laradmin\Transformers\Response\RelationshipTransformer;

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
 * @property string|null $relation_image_conversion
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

    public function getRelationKeyName(EloquentModel $model = null)
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
                'conversion' => isset($image[1]) ? $image[1] : null
            ];
        }

        return $image;
    }

    public function getRelationImageConversionAttribute()
    {
        return data_get($this->relation_image, 'conversion', null);
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
        }
        elseif (! $model) {
            return false;
        }

        if(! $relation = $this->getRelationClass($model)) {
            return false;
        }

        return $relation->getRelated();
    }

    /**
     * @param EloquentModel $model
     * @return array
     * @throws \Shemi\Laradmin\Exceptions\ManagerDoesNotExistsException
     */
    public function transformRelationModel(EloquentModel $model)
    {
        return (new RelationshipTransformer())
            ->init($this, $this->key, $model)
            ->asRelationModel($model);
    }

}