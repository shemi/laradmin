<?php

namespace Shemi\Laradmin\Models;

use Shemi\Laradmin\Data\Model;

class Type extends Model
{
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

    public function getOnlyFields($fields = null)
    {
        $fields = $fields === null ? $this->panels : $fields;
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

            if(static::isValidField($field)) {
                $newFields->push(Field::fromArray($field));
            }
        }

        return $newFields;
    }

    public function getBrowseColumnsAttribute()
    {
        $fields = $this
            ->getOnlyFields()
            ->reject(function($field) {
                return ! $field->isVisibleOn('browse');
            })
            ->sortBy('browse_order')
            ->values();

        return $fields;
    }

    public function getRecordsPerPageAttribute($key)
    {
        return $key ?: 15;
    }

}