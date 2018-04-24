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
        return data_get($this->browse_settings, 'searchable', false) && ! $this->is_relationship;
    }

    public function getSearchComparisonAttribute()
    {
        return data_get($this->browse_settings, 'search_comparison', '=');
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
                    return $rModel->getAttribute($this->relationship['label']);
                }

                return $model->getAttribute($this->key);

            case 'date':
            case 'datetime':
            case 'time':
                $value = $model->getAttribute($this->key);
                $format = "d/m/Y";

                try {
                    $value = \Carbon\Carbon::parse($value);
                    $value->tz('Asia/Jerusalem');
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
                if($this->is_relationship) {
                    if(! $model->{$this->key}) {
                        return 'Relation error!';
                    }

                    return $model->{$this->key}
                        ->pluck($this->relationship['label'])
                        ->implode(', ');
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