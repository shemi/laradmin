<?php

namespace Shemi\Laradmin\Models;

use Shemi\Laradmin\Data\Model;

use \Illuminate\Database\Eloquent\Model as EloquentModel;

class Field extends Model
{
    protected $dataable = false;

    protected $fillable = [
        'label',
        'key',
        'default_value',
        'nullable',
        'type',
        'visibility',
        'sortable',
        'searchable',
        'search_comparison',
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

    public function getIsNumericAttribute()
    {
        return in_array($this->field_type, ['number']);
    }

    public function getModelCastType(EloquentModel $model)
    {
        return trim(strtolower($model->getCasts()[$this->key]));
    }

    public function getDefaultValue(EloquentModel $model)
    {
        if($this->default_value !== null || $this->nullable) {
            return $this->default_value;
        }

        if(in_array($this->key, $model->getDates())) {
            return "";
        }

        $type = $model->hasCast($this->key) ? $this->getModelCastType($model) : $this->type;

        switch ($type) {
            case 'int':
            case 'integer':
            case 'real':
            case 'float':
            case 'double':
            case 'bool':
            case 'boolean':
                return null;
            case 'string':
            case 'date':
            case 'datetime':
            case 'timestamp':
            case 'input':
                return "";
            case 'object':
            case 'group':
                return (object) [];
            case 'array':
            case 'json':
            case 'collection':
            case 'repeater':
                return (array) [];
            default:
                return null;
        }

    }

    public function getModelValue(EloquentModel $model)
    {
        if(! $model->exists || ! $model->offsetExists($this->key)) {
            return $this->getDefaultValue($model);
        }

        $value = $model->getAttribute($this->key);

        if(in_array($this->key, $model->getDates())) {
            $type = "date";
        } else {
            $type = $model->hasCast($this->key) ? $this->getModelCastType($model) : $this->type;
        }

        switch ($type) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'real':
            case 'float':
            case 'double':
                return (float) $value;
            case 'bool':
            case 'boolean':
                return (boolean) $value;
            case 'string':
            case 'input':
                return (string) $value;
            case 'date':
            case 'datetime':
            case 'timestamp':
                return $this->getDateValue($value);
            case 'object':
            case 'group':
                return (object) $value;
            case 'array':
            case 'json':
            case 'collection':
            case 'repeater':
                return (array) $value;
            default:
                return $value;
        }
    }

    public function getDateValue($value)
    {
        if(is_string($value)) {
            return $value;
        }

        if($value instanceof \DateTime) {
            return $value->format('c');
        }

        return (string) $value;
    }

    public static function isValidField($field)
    {
        $field = (array) $field;

        return is_array($field) &&
            array_key_exists('key', $field) &&
            ! empty($field['key']);
    }

}