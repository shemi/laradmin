<?php

namespace Shemi\Laradmin\Models\Traits;

use Carbon\Carbon;
use \Illuminate\Database\Eloquent\Model as EloquentModel;
use Shemi\Laradmin\Contracts\FieldHasBrowseValue;
use Shemi\Laradmin\Models\Field;

/**
 * Shemi\Laradmin\Models\Traits\FieldHasBrowseSettings
 *
 * @property int $id
 * @property array|null $browse_settings
 * @property boolean $is_relationship
 * @property array|null $relationship
 * @property string $key
 * @property string $type
 * @property string $label
 * @property integer $order
 * @property string $browse_key
 * @property string $full_browse_key
 * @property string $browse_label
 * @property boolean $sortable
 * @property boolean $searchable
 * @property boolean $search_comparison
 * @property boolean $filterable
 */

trait HasBrowseSettings
{

    public function getBrowseOrderAttribute()
    {
        return data_get($this->browse_settings, 'order', 999);
    }

    public function getBrowseKeyAttribute()
    {
        $prefix = "";

        if($this->is_sub_field && $this->parent) {
            $prefix = "{$this->parent->browse_key}.";
        }

        return $prefix.data_get($this->browse_settings, 'key', $this->key);
    }

    public function getFullBrowseKeyAttribute()
    {
        $prefix = "";

        if($this->is_sub_field && $this->parent) {
            $prefix = "{$this->parent->browse_key}." . ($this->is_repeater_sub_field ? '*.' : '');
        }

        return $prefix.data_get($this->browse_settings, 'key', $this->key);
    }

    public function getBrowseLabelAttribute()
    {
        return data_get($this->browse_settings, 'label', $this->label);
    }

    public function getSortableAttribute()
    {
        return data_get($this->browse_settings, 'sortable', false) && ! $this->is_relationship;
    }

    public function getSearchableAttribute()
    {
        return data_get($this->browse_settings, 'searchable', false);
    }

    public function getSearchComparisonAttribute()
    {
        return data_get($this->browse_settings, 'search_comparison', '=');
    }

    public function getFilterableAttribute()
    {
        return $this->is_relationship &&
            $this->searchable &&
            $this->search_comparison !== 'like';
    }

    /**
     * @param EloquentModel $model
     * @return array
     */
    public function getMediaBrowseValue(EloquentModel $model)
    {
        $value = [];
        $mediaCollection = $this->media_collection;

        if($this->is_sub_field && $this->parent) {

        }

//        route('laradmin.serve', [
//            'mediaId' => $media->id,
//            'fileName' => $media->name,
//            'pc' => $this->getTemplateOption('preview_conversion', null)
//        ])

        return $value;
    }

    /**
     * @param EloquentModel $model
     * @return array|bool|string
     */
    public function getBrowseValue(EloquentModel $model)
    {
        if(in_array($this->key, $model->getHidden())) {
            return "";
        }

        if($this->formFieldsManager()->exists($this->type)) {
            $formField = $this->formField();

            if($formField instanceof FieldHasBrowseValue) {
                return $formField->renderBrowseValue($this, $model);
            }
        }

        if($this->is_media) {
            return $this->getMediaBrowseValue($model);
        }

        $modelValue = data_get($model, $this->full_browse_key);

        switch ($this->type) {

            case 'select':
            case 'radio':
                if($this->is_relationship && $modelValue) {
                    $labels = [];

                    foreach ($this->relation_labels as $label) {
                        $labels[] = $modelValue->getAttribute($label);
                    }

                    return [$labels];
                }

                return $modelValue;

            case 'time':
            case 'date':
            case 'datetime':
                if(is_array($modelValue) && array_key_exists('date', $modelValue)) {
                    $modelValue = Carbon::parse($modelValue['date'], 'UTC');
                }

                if(is_string($modelValue)) {
                    $modelValue = Carbon::parse($modelValue);
                }

                if(! $modelValue instanceof Carbon) {
                    return null;
                }

                $modelValue->timezone('UTC');

                return $modelValue->toIso8601String();

            case 'select_multiple':
            case 'checkboxes':
            case 'repeater':
            case 'relationship':
                if($this->is_relationship) {
                    if(! $modelValue) {
                        return 'Relation error!';
                    }

                    return $modelValue
                        ->map(function($model) {
                            $labels = [];

                            foreach ($this->relation_labels as $label) {
                                $labels[] = $model->getAttribute($label);
                            }

                            return $labels;
                        });
                }

                return $model->getAttribute($this->key);

            case 'checkbox':
            case 'switch':
                return (bool) $modelValue;

        }

        return $modelValue;
    }

}