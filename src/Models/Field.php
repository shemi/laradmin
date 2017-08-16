<?php

namespace Shemi\Laradmin\Models;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Shemi\Laradmin\Data\Model;

use \Illuminate\Database\Eloquent\Model as EloquentModel;
use Shemi\Laradmin\FormFields\FormField;

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
        'is_repeater_field',
        'fields',
        'form_prefix'
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
        if($this->is_repeater_field || in_array($this->type, ['switch', 'checkbox'])) {
            return false;
        }

        return $value !== null ? $value : true;
    }

    public function getFormPrefixAttribute($value)
    {
        return $value !== null ? $value : "form.";
    }

    public function getFieldsAttribute($value)
    {
        $fields = collect($value);

        $fields = $fields->transform(function($rawField) {
            $rawField['is_repeater_field'] = true;
            $rawField['form_prefix'] = "props.row.";

            return static::fromArray($rawField);
        });

        return $fields;
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
        if(in_array($this->type, ['checkboxes'])) {
            return true;
        }

        return $this->getTemplateOption('grouped', false);
    }

    public function getIsGroupMultilineAttribute() {
        if(in_array($this->type, ['checkboxes'])) {
            return true;
        }

        return $this->getTemplateOption('group_multiline', false);
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

    public function getIsMediaAttribute()
    {
        return in_array($this->type, ['images', 'files', 'file', 'image']);
    }

    public function getIsSingleMediaAttribute()
    {
        return in_array($this->type, ['file', 'image']);
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

            case 'switch':
            case 'checkbox':
                return false;

            case 'select':
            case 'radio':
                return null;

            case 'object':
            case 'group':
                return (object) [];

            case 'select_multiple':
            case 'checkboxes':
            case 'repeater':
            case 'files':
                return (array) [];

            default:
                return null;
        }

    }

    protected function transformRelationCollection(Collection $collection, EloquentModel $model)
    {
        if(in_array($this->type, ['checkboxes'])) {
            return $collection->pluck($this->relationship['key']);
        }

        return $collection->transform(function($model) {
            return [
                'key' => $model->getAttribute($this->relationship['key']),
                'label' => $model->getAttribute($this->relationship['label'])
            ];
        });
    }

    public function getModelValue(EloquentModel $model)
    {
        if(! $model->exists() || (! $model->offsetExists($this->key) && ! $this->is_media)) {
            $value = $this->getDefaultValue($model);

            return $this->transformResponse($value);
        }

        if($this->is_relationship) {
            $value = $model->getAttribute($this->key);

            if ($value instanceof Collection) {
                $value = $this->transformRelationCollection($value, $model);
            } elseif ($value instanceof EloquentModel) {
                $value = $value->getAttribute($this->relationship['key']);
            }

        } elseif($this->is_media) {
            $value = $model->getMedia($this->key);

            if($this->is_single_media) {
                $value = $value->isEmpty() ? [] : $this->transformMediaModel($value->first());
            } else {
                $value = $this->transformMediaCollection($value);
            }

        } else {
            $value = $model->getAttribute($this->key);
        }

        return $this->transformResponse($value);
    }

    public function isDate()
    {
        return in_array($this->type, ['date', 'datetime', 'time']);
    }

    public function transformMediaCollection(Collection $collection)
    {
        return $collection->transform(function(Media $media) {
            return $this->transformMediaModel($media);
        })->toArray();
    }

    public function transformMediaModel(Media $media)
    {
        return [
            'id' => $media->id,
            'name' => $media->name,
            'size' => $media->size,
            'ext' => $media->extension,
            'alt' => $media->getCustomProperty('alt'),
            'caption' => $media->getCustomProperty('caption'),
        ];
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

            case 'checkbox':
            case 'switch':
            return (bool) $model->getAttribute($this->key);
                break;

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

    /**
     * @return FormField
     */
    public function formField()
    {
        return app('laradmin')->formField($this->type);
    }

    public function transformRequest($value)
    {
        return $this->formField()->transformRequest($this, $value);
    }

    public function transformResponse($value)
    {
        if(! app('laradmin')->formFieldExists($this->type)) {
            return $value;
        }

        return $this->formField()->transformResponse($this, $value);
    }

    /**
     * @param Type $type
     * @param EloquentModel $model
     * @param array $data
     * @return string
     */
    public function render(Type $type, EloquentModel $model, $data)
    {
        if($this->is_relationship && array_key_exists($this->key, $data)) {
            $this->options = $data[$this->key];
        }

        return $this->formField()->handle($this, $type, $model, $data);
    }

}