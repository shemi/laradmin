<?php

namespace Shemi\Laradmin\Models\Traits;

use \Illuminate\Database\Eloquent\Model as EloquentModel;
use Shemi\Laradmin\Contracts\FieldHasBrowseValue;

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
        return data_get($this->browse_settings, 'key', $this->key);
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

    public function getBrowseValue(EloquentModel $model)
    {
        if(in_array($this->key, $model->getHidden())) {
            return "";
        }

        if(app('laradmin')->formFieldExists($this->type)) {
            $formField = $this->formField();

            if($formField instanceof FieldHasBrowseValue) {
                return $formField->renderBrowseValue($this, $model);
            }
        }

        switch ($this->type) {

            case 'text':
            case 'text_area':
            case 'number':
                return $model->getAttribute($this->key);

            case 'select':
            case 'radio':
                if($this->is_relationship && $rModel = $model->{$this->key}) {
                    $labels = "";

                    foreach ($this->relation_labels as $label) {
                        $labels .= $rModel->getAttribute($label).', ';
                    }

                    return trim($labels, ', ');
                }

                return $model->getAttribute($this->key);

            case 'time':
            case 'date':
            case 'datetime':
                $value = $model->getAttribute($this->key);

                if($this->type === 'time') {
                    $format = "H:i";
                } else {
                    $format = "d/m/Y";
                }

                try {
                    $value = \Carbon\Carbon::parse($value);
                    $value->tz(config('app.timezone'));
                } catch (\Exception $e) {}

                if(isset($this->browse_settings['date_format']) && $this->browse_settings['date_format']) {
                    $format = addslashes($this->browse_settings['date_format']);
                }

                if($value instanceof \DateTime) {
                    $value = $value->format($format);
                }

                return $value;

            case 'select_multiple':
            case 'checkboxes':
            case 'repeater':
            case 'relationship':
                if($this->is_relationship) {
                    if(! $model->{$this->key}) {
                        return 'Relation error!';
                    }

                    return $model->{$this->key}
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
                return (bool) $model->getAttribute($this->key);

                break;

            default:
                return $model->getAttribute($this->key);

        }
    }

}