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
        'fields',
        'records_per_page',
        'relationships'
    ];

    public static function whereSlug($slug)
    {
        return static::where('slug', $slug)->first();
    }

    public function hasModel()
    {
        return ! empty($this->model);
    }

    public function getOnlyFields($fields)
    {
        $fields = collect($fields);
        $newFields = collect([]);

        foreach ($fields as $field) {
            if(! is_array($field) || ! Field::isValidField($field)) {
                continue;
            }

            $newFields->push(Field::fromArray($field));
        }

        return $newFields;
    }

    public function getFieldsAttribute($value)
    {
        if($this->_fields) {
            return $this->_fields;
        }

        $this->_fields = $this->getOnlyFields($value);

        return $this->_fields;
    }

    public function getBrowseColumnsAttribute()
    {
        $fields = $this->fields
            ->reject(function($field) {
                return ! $field->isVisibleOn('browse');
            })
            ->values();

        return $fields;
    }

    public function getSearchableFieldsAttribute()
    {
        $fields = $this->fields
            ->reject(function($field) {
                return ! $field->searchable;
            })
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

    public function getHasRelationshipsAttribute()
    {
        return $this->relationships && ! empty($this->relationships);
    }

}