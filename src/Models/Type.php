<?php

namespace Shemi\Laradmin\Models;

use Shemi\Laradmin\Data\Model;

use \Illuminate\Database\Eloquent\Model as EloquentModel;

class Type extends Model
{
    protected $_fields;

    protected $fillable = [
        'name',
        'model',
        'slug',
        'public',
        'controller',
        'panels',
        'records_per_page',
    ];

    public static function whereSlug($slug)
    {
        return static::where('slug', $slug)->first();
    }

    public function hasModel()
    {
        return ! empty($this->model);
    }

    protected static function isGroup($field)
    {
        return is_array($field) &&
            array_key_exists('fields', $field) &&
            ! empty($field['fields']);
    }

    protected static function isValidField($field)
    {
        return is_array($field) &&
            array_key_exists('key', $field) &&
            ! empty($field['key']);
    }

    public function getOnlyFields($fields)
    {
        $fields = collect($fields);
        $newFields = collect([]);

        foreach ($fields as $field) {
            if(! is_array($field)) {
                continue;
            }

            if(static::isGroup($field) && $groupFields = $this->getOnlyFields($field['fields'])) {
                $newFields = $newFields->merge($groupFields);

                continue;
            }

            if(Field::isValidField($field)) {
                $newFields->push(Field::fromArray($field));
            }
        }

        return $newFields;
    }

    public function getFieldsAttribute()
    {
        if($this->_fields) {
            return $this->_fields;
        }

        $this->_fields = $this->getOnlyFields($this->panels);

        return $this->_fields;
    }

    public function getBrowseColumnsAttribute()
    {
        $fields = $this->fields
            ->reject(function($field) {
                return ! $field->isVisibleOn('browse');
            })
            ->sortBy('browse_order')
            ->values();

        return $fields;
    }

    public function getSearchableFieldsAttribute()
    {
        $fields = $this->fields
            ->reject(function($field) {
                return ! $field->searchable;
            })
            ->sortBy('browse_order')
            ->values();

        return $fields;
    }

    public function getRecordsPerPageAttribute($key)
    {
        return $key ?: 15;
    }

    public function getModelArray(EloquentModel $model)
    {
        $array = [];

        foreach ($this->fields as $field) {
            $array[$field->key] = $field->getModelValue($model);
        }

        return $array;
    }

}