<?php

namespace Shemi\Laradmin\Models;

use Shemi\Laradmin\Data\Model;

class Field extends Model
{
    protected $dataable = false;

    protected $fillable = [
        'label',
        'key',
        'type',
        'validation',
        'visibility',
        'template_options',
        'browse_settings',
    ];

    public static function fromArray($rawField)
    {
        return (new static)->newFromManager($rawField);
    }

    public function isVisibleOn($view)
    {
        if(! isset($this->visibility) || empty($this->visibility)) {
            return false;
        }

        return in_array($view, $this->visibility);
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
        return data_get($this->browse_settings, 'sortable', false);
    }

    public function getSearchableAttribute()
    {
        return data_get($this->browse_settings, 'searchable', false);
    }

    public function getSearchComparisonAttribute()
    {
        return data_get($this->browse_settings, 'search_comparison', '=');
    }

    public function getFieldTypeAttribute()
    {
        return data_get($this->template_options, 'type', 'text');
    }

    public function getIsNumericAttribute()
    {
        return in_array($this->field_type, ['number']);
    }

}