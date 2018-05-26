<?php

namespace Shemi\Laradmin\Models\Traits;

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

        if($this->is_sub_field && $this->parent && ! $this->is_repeater_sub_field) {
            $prefix = "{$this->parent->browse_key}.";
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
     * @return array|bool|string
     */
    public function getBrowseValue(EloquentModel $model)
    {
        if($model instanceof EloquentModel && in_array($this->key, $model->getHidden())) {
            return "";
        }

        if($model instanceof EloquentModel) {
            if($this->formFieldsManager()->exists($this->type)) {
                $formField = $this->formField();

                if($formField instanceof FieldHasBrowseValue) {
                    return $formField->renderBrowseValue($this, $model);
                }
            }
        }

        $modelValue = data_get($model, $this->browse_key);

        switch ($this->type) {

            case 'select':
            case 'radio':
                if($this->is_relationship && $modelValue) {
                    $labels = "";

                    foreach ($this->relation_labels as $label) {
                        $labels .= $modelValue->getAttribute($label).', ';
                    }

                    return trim($labels, ', ');
                }

                return $modelValue;

            case 'time':
            case 'date':
            case 'datetime':
                if($this->type === 'time') {
                    $format = "H:i";
                } else {
                    $format = "d/m/Y";
                }

                try {
                    $modelValue = \Carbon\Carbon::parse($modelValue);
                    $modelValue->tz(config('app.timezone'));
                } catch (\Exception $e) {}

                if(isset($this->browse_settings['date_format']) && $this->browse_settings['date_format']) {
                    $format = addslashes($this->browse_settings['date_format']);
                }

                if($modelValue instanceof \DateTime) {
                    $modelValue = $modelValue->format($format);
                }

                return $modelValue;

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
                            $labels = "";

                            foreach ($this->relation_labels as $label) {
                                $labels .= $model->getAttribute($label).', ';
                            }

                            return trim($labels, ', ');
                        })
                        ->implode(' <b>|</b> ');
                }

                return $model->getAttribute($this->key);

            case 'checkbox':
            case 'switch':
                return (bool) $modelValue;

            default:
                return $modelValue;

        }
    }

}