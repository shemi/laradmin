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
 * @property string $full_key
 * @property string $validation_key
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
 * @property boolean $is_sub_field
 * @property boolean $is_password
 * @property boolean $is_support_sub_fields
 * @property boolean $is_repeater_sub_field
 * @property Collection|null $fields
 * @property string $form_prefix
 * @property boolean $read_only
 * @property Collection $raw_fields
 */
class Field extends Model
{

    use InteractsWithRelationship,
        InteractsWithMedia,
        HasBrowseSettings,
        HasTemplateOptions,
        InteractsWithFormField;

    const OBJECT_TYPE = 'field';

    protected $dataable = false;

    protected $keyType = 'string';

    protected $jsonIgnore = [
        'parent'
    ];

    /**
     * @var Panel $_panel
     */
    protected $_panel;

    /**
     * @var Type $_type
     */
    protected $_type;

    public $parent = null;

    public $is_sub_field = false;

    protected $fillable = [
        'id',
        'label',
        'key',
        'show_label',
        'default_value',
        'read_only',
        'nullable',
        'type',
        'validation',
        'visibility',
        'options',
        'template_options',
        'browse_settings',
        'relationship',
        'fields',
        'media'
    ];

    public static function fromArray($attributes, Panel $panel, Model $type)
    {
        $model = new static;

        $model->setPanel($panel);
        $model->setType($type);

        $localAttributes = [
            'parent' => null,
            'is_sub_field' => false

        ];

        foreach ($localAttributes as $attribute => $defaultValue) {
            $model->{$attribute} = array_get($attributes, $attribute, $defaultValue);

            array_forget($attributes, $attribute);
        }

        $model->setRawAttributes((array) $attributes, true);

        return $model;
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
        if($this->is_repeater_sub_field || in_array($this->type, ['switch', 'checkbox'])) {
            return false;
        }

        return $value !== null ? $value : true;
    }

    public function getFullKeyAttribute()
    {
        $prefix = "";

        if($this->parent) {
            $prefix = $this->parent->full_key.".";
        }

        return $prefix.$this->key;
    }

    public function getIsRepeaterSubFieldAttribute()
    {
        return $this->is_sub_field && $this->parent && $this->parent->type === 'repeater';
    }

    public function getIsSupportSubFieldsAttribute()
    {
        return $this->formField()->isSupportingSubFields();
    }

    public function getValidationKeyAttribute()
    {
        return $this->formField()->getValidationKey($this, $this->parent);
    }

    public function getIsPasswordAttribute()
    {
        return $this->field_type === 'password';
    }

    public function getFormPrefixAttribute()
    {
        return $this->formField()->getFormPrefix($this);
    }

    public function getFieldsAttribute($value)
    {
        return collect($value)->transform(function($rawField) {
            $rawField['is_sub_field'] = true;
            $rawField['parent'] = $this;

            return static::fromArray($rawField, $this->getPanel(), $this->getType());
        });
    }

    /**
     * @return Collection
     */
    public function getSubFields()
    {
        $localFields = $this->fields;

        if($this->type !== 'repeater' || ! $this->has_relationship_type) {
            return $localFields;
        }

        $relationType = $this->relationship_type;
        $exclude = array_get($this->relationship, 'exclude', []);

        return $relationType->fields
            ->reject(function(Field $field) use ($exclude) {
                return in_array($field->key, $exclude) || $field->read_only;
            })
            ->map(function(Field $field) use ($localFields) {
                $field = $localFields->where('key', $field->key)->first() ?: $field;

                $field->is_sub_field = true;
                $field->parent = $this;

                return $field;
            });
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
                $object = [];

                if($this->is_support_sub_fields) {
                    /** @var Field $subField */
                    foreach ($this->getSubFields() as $subField) {
                        $object[$subField->key] = $subField->getDefaultValue();
                    }
                }

                return $object;

            case 'select_multiple':
            case 'checkboxes':
            case 'repeater':
            case 'files':
                return (array) [];

            default:
                return null;
        }

    }

    public function getModelValue(EloquentModel $model, $key = null)
    {
        $key = $key ?: $this->key;

        if($this->is_password || in_array($key, $model->getHidden())) {
            return "";
        }

        if(! $model->exists || (! $model->offsetExists($key) && ! $this->is_media)) {
            $value = $this->getDefaultValue();

            return $this->transformResponse($value);
        }

        if($this->is_relationship) {
            $value = $model->getAttribute($key);

            if ($value instanceof Collection) {
                $value = $this->transformRelationCollection($value, $model);
            }
            elseif (! $this->is_support_sub_fields && $value instanceof EloquentModel) {
                $value = $value->getAttribute($this->getRelationKeyName($model));
            }
            elseif ($this->is_support_sub_fields && $value instanceof EloquentModel) {
                $newValue = [];

                /** @var Field $subField */
                foreach ($this->getSubFields() as $subField) {
                    $newValue[$subField->key] = $subField->getModelValue($value);
                }

                $newValue[$value->getKeyName()] = $value->getKey();

                $value = $newValue;
            }

        } elseif($this->is_media) {
            if($this->getType() instanceof SettingsPage) {
                $value = $model->getMedia();
            } else {
                $value = $model->getMedia($this->key);
            }

            if($this->is_single_media) {
                $value = $value->isEmpty() ? null : $this->transformMediaModel($value->first());
            } else {
                $value = $this->transformMediaCollection($value);
            }

        } else {
            $value = $model->getAttribute($key);
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
        if ($field instanceof Field) {
            return true;
        }

        $field = (array) $field;

        if(isset($field['object_type'])) {
            return $field['object_type'] === static::OBJECT_TYPE;
        }

        return is_array($field) && array_key_exists('key', $field) && ! empty($field['key']);
    }

    public function toBuilder()
    {
        $fields = [
            'id' => 'string',
            'label' => 'string',
            'key' => 'string',
            'show_label' => 'bool',
            'default_value' => null,
            'nullable' => null,
            'type' => 'string',
            'validation' => 'array',
            'visibility' => 'array',
            'options' => 'array',
            'template_options' => 'object',
            'read_only' => 'bool',
            'browse_settings' => 'object',
            'relationship' => 'object|bool',
            'media' => 'object',
            'tab_id' => null
        ];

        $array = [];

        foreach ($fields as $key => $cast) {
            $array[$key] = $this->castBuilderAttribute($this->{$key}, $cast);
        }

        if($this->fields->isNotEmpty()) {
            $array['fields'] = $this->fields->map(function($field) {
                return $field->toBuilder();
            });
        }

        $array['object_type'] = static::OBJECT_TYPE;

        return $array;
    }

    /**
     * @return Panel
     */
    public function getPanel(): Panel
    {
        return $this->_panel;
    }

    /**
     * @param Panel $panel
     * @return Field
     */
    public function setPanel(Panel $panel)
    {
        $this->_panel = $panel;

        return $this;
    }

    /**
     * @return Type
     */
    public function getType(): Model
    {
        return $this->_type;
    }

    /**
     * @param Model $type
     * @return Field
     */
    public function setType(Model $type)
    {
        $this->_type = $type;

        return $this;
    }


}