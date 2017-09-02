<?php

namespace Shemi\Laradmin\Models;

use Illuminate\Support\Collection;
use Shemi\Laradmin\Data\Model;

use \Illuminate\Database\Eloquent\Model as EloquentModel;
use Shemi\Laradmin\Models\Traits\HasBrowseSettings;
use Shemi\Laradmin\Models\Traits\InteractsWithFormField;
use Shemi\Laradmin\Models\Traits\InteractsWithMedia;
use Shemi\Laradmin\Models\Traits\InteractsWithRelationship;
use Shemi\Laradmin\Models\Traits\HasTemplateOptions;

/**
 * Shemi\Laradmin\Models\Field
 *
 * @property string $label
 * @property string $key
 * @property Field|null $parent
 * @property boolean $show_label
 * @property mixed $default_value
 * @property mixed $nullable
 * @property string $type
 * @property array $validation
 * @property array $visibility
 * @property array $options
 * @property array $template_options
 * @property array $browse_settings
 * @property array $relationship
 * @property boolean $is_repeater_field
 * @property array|null $fields
 * @property string $form_prefix
 */
class Field extends Model
{

    use InteractsWithRelationship,
        InteractsWithMedia,
        HasBrowseSettings,
        HasTemplateOptions,
        InteractsWithFormField;

    protected $dataable = false;

    protected $fillable = [
        'label',
        'key',
        'parent',
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
        'form_prefix',
        'media'
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

    public function getValidationKeyAttribute()
    {
        if($this->is_repeater_field) {
            $parent = $this->parent;

            return "{$parent->validation_key}.'+ props.index +'.{$this->key}";
        }

        return $this->key;
    }

    public function getFieldsAttribute($value)
    {
        $fields = collect($value);

        $fields = $fields->transform(function($rawField) {
            $rawField['is_repeater_field'] = true;
            $rawField['parent'] = $this;
            $rawField['form_prefix'] = "props.row.";

            return static::fromArray($rawField);
        });

        return $fields;
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

    public function getModelCastType(EloquentModel $model)
    {
        return trim(strtolower($model->getCasts()[$this->key]));
    }

    public function getDefaultValue()
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
            case 'image':
            case 'file':
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

    public function getModelValue(EloquentModel $model)
    {
        if($this->is_password || in_array($this->key, $model->getHidden())) {
            return "";
        }

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
                $value = $value->isEmpty() ? null : $this->transformMediaModel($value->first());
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

    public function getVueFilter()
    {
        if($this->isDate()) {
            return "date()";
        }

        return null;
    }

    public static function isValidField($field)
    {
        $field = (array) $field;

        return is_array($field) &&
            array_key_exists('key', $field) &&
            ! empty($field['key']);
    }

}