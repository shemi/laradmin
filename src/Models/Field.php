<?php

namespace Shemi\Laradmin\Models;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Shemi\Laradmin\Data\Model;

use \Illuminate\Database\Eloquent\Model as EloquentModel;

class Field extends Model
{
    protected $dataable = false;

    protected $fillable = [
        'label',
        'key',
        'show_label',
        'default_value',
        'nullable',
        'type',
        'validation',
        'visibility',
        'options',
        'template_options',
        'browse_settings',
        'relationship',
    ];

    public static function fromArray($rawField)
    {
        return (new static)->newFromManager($rawField);
    }

    /**
     * @param array|string $views
     * @return bool
     */
    public function isVisibleOn($views)
    {
        if(! isset($this->visibility) || empty($this->visibility)) {
            return false;
        }

        foreach ((array) $views as $view) {
            if(in_array($view, $this->visibility)) {
                return true;
            }
        }

        return false;
    }

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

    public function getShowLabelAttribute($value)
    {
        return $value !== null ? $value : true;
    }

    public function getFieldTypeAttribute()
    {
        return $this->getTemplateOption('type', 'text');
    }

    public function getPlaceholderAttribute()
    {
        return $this->getTemplateOption('placeholder');
    }

    public function getIconAttribute()
    {
        return $this->getTemplateOption('icon', '');
    }

    public function getFieldSizeAttribute()
    {
        return $this->getTemplateOption('size', 'default');
    }

    public function getIsGroupedAttribute() {
        return $this->getTemplateOption('grouped', false);
    }

    public function getTemplatePositionAttribute() {
        return $this->getTemplateOption('position', 'is-left');
    }

    public function getMaxLengthAttribute() {
        return $this->getTemplateOption('max_length', 0);
    }

    public function getTemplateOption($key, $default = null)
    {
        return data_get($this->template_options, $key, $default);
    }

    public function getOptionsAttribute($value)
    {
        if(is_array($value)) {
            return $value;
        }

        return [];
    }

    public function getIsNumericAttribute()
    {
        return in_array($this->field_type, ['number', 'float']);
    }

    public function getIsRelationshipAttribute()
    {
        return $this->relationship &&
               is_array($this->relationship) &&
               ! empty($this->relationship);
    }

    public function getModelCastType(EloquentModel $model)
    {
        return trim(strtolower($model->getCasts()[$this->key]));
    }

    public function getDefaultValue(EloquentModel $model)
    {
        if($this->default_value !== null) {
            return $this->default_value;
        }

        if($this->nullable) {
            return null;
        }

        switch ($this->type) {
            case 'number':
            case 'text':
            case 'text_area':
            case 'date':
            case 'datetime':
                return "";

            case 'select':
            case 'radio':
                return null;

            case 'object':
            case 'group':
                return (object) [];

            case 'select_multiple':
            case 'checkboxes':
            case 'repeater':
                return (array) [];

            default:
                return null;
        }

    }

    public function getModelValue(EloquentModel $model)
    {
        if(! $model->exists() || ! $model->offsetExists($this->key)) {
            return $this->getDefaultValue($model);
        }

        if($this->is_relationship) {
            $value = $model->getAttribute($this->key);

            if($value instanceof Collection) {
                return $value->transform(function($model) {
                    return [
                        'key' => $model->getAttribute($this->relationship['key']),
                        'label' => $model->getAttribute($this->relationship['label'])
                    ];
                });
            }

            if($value instanceof EloquentModel) {
                return $value->getAttribute($this->relationship['key']);
            }
        }

        return $model->getAttribute($this->key);
    }

    public function isDate()
    {
        return in_array($this->type, ['date', 'datetime', 'time']);
    }

    public function getVueFilter()
    {
        if($this->isDate()) {
            return "date()";
        }

        return null;
    }

    public function getBrowseValue(EloquentModel $model)
    {
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

                if(isset($this->browse_settings['date_format']) && $this->browse_settings['date_format']) {
                    return \Carbon\Carbon::parse($value)->format(
                        addslashes($this->browse_settings['date_format'])
                    );
                }

                return $value;

            case 'select_multiple':
            case 'checkboxes':
            case 'repeater':
                if($this->is_relationship) {
                    return $model->{$this->key}
                        ->pluck($this->relationship['label'])
                        ->implode(', ');
                }

                return $model->getAttribute($this->key);

            default:
                return $model->getAttribute($this->key);

        }
    }

    public function getRelationModelClass(EloquentModel $model)
    {
        if(! $this->is_relationship || ! method_exists($model, $this->key)) {
            return false;
        }

        $relation = $model->{$this->key}();

        if(! ($relation instanceof Relation)) {
            return false;
        }

        return $relation->getRelated();
    }

    public static function isValidField($field)
    {
        $field = (array) $field;

        return is_array($field) &&
            array_key_exists('key', $field) &&
            ! empty($field['key']);
    }

    public function render(Type $type, EloquentModel $model, $data)
    {
        if($this->is_relationship && array_key_exists($this->key, $data)) {
            $this->options = $data[$this->key];
        }

        return app('laradmin')->formField($this, $type, $model, $data);
    }

}