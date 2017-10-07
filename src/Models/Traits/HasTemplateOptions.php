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
*/

trait HasTemplateOptions
{

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

    public function getShowIfAttribute() {
        return $this->getTemplateOption('show_if');
    }

    public function getTemplateOption($key, $default = null)
    {
        return data_get($this->template_options, $key, $default);
    }

}