<?php

namespace Shemi\Laradmin\Models\Traits;

/**
 * Shemi\Laradmin\Models\Traits\FieldHasTemplateOptions
 *
 * @property array|null $template_options
 * @property string $key
 * @property string $type
 * @property string $field_type
 * @property string $placeholder
 * @property string $icon
 * @property string $field_size
 * @property boolean $is_grouped
 * @property boolean $is_group_multiline
 * @property mixed $show_if
 * @property string $template_position
 * @property integer $max_length
 * @property boolean $is_horizontal
*/

trait HasTemplateOptions
{

    public static $uninheritableOptions = [
        'show_if', 'max_length', 'placeholder',
        'type', 'icon'
    ];

    static public $forceGroupedTypes = ['checkboxes'];

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
        if(in_array($this->type, static::$forceGroupedTypes)) {
            return true;
        }

        return $this->getTemplateOption('grouped', false);
    }

    public function getIsGroupMultilineAttribute() {
        if(in_array($this->type, static::$forceGroupedTypes)) {
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

    public function getShowIfAttribute() {
        return $this->getTemplateOption('show_if');
    }

    public function getIsHorizontalAttribute()
    {
        if($this->is_repeater_sub_field) {
            return false;
        }

        return $this->getTemplateOption('horizontal', false);
    }

    public function canInheritFromParent()
    {
        return $this->is_sub_field && $this->parent;
    }

    public function canInherit($key)
    {
        return $this->canInheritFromParent() &&
            ! isset($this->template_options[$key]) &&
            ! in_array($key, static::$uninheritableOptions);
    }

    public function isHeirToChildren($key)
    {
        return $this->is_support_sub_fields && ! in_array($key, static::$uninheritableOptions);
    }

    public function getTemplateOption($key, $default = null)
    {
        $parentValue = $default;

        if($this->isHeirToChildren($key)) {
            return $default;
        }

        if($this->canInherit($key)) {
            $parentValue = data_get($this->parent->template_options, $key, $default);
        }

        return data_get($this->template_options, $key, $parentValue);
    }

}