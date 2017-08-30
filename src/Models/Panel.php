<?php

namespace Shemi\Laradmin\Models;

use Shemi\Laradmin\Data\Model;

use \Illuminate\Database\Eloquent\Model as EloquentModel;

class Panel extends Model
{
    protected $dataable = false;

    protected $fillable = [
        'id',
        'title',
        'position',
        'is_main_meta',
        'has_container',
        'style',
        'fields'
    ];

    public function getStyleAttribute($value)
    {
        $style = (object) $value ? $value : [];

        return json_encode($style, JSON_UNESCAPED_UNICODE);
    }

    public function getHasContainerAttribute($value)
    {
        return $value === null ? true : $value;
    }

    public static function isValidPanel($panel)
    {
        if($panel instanceof Panel) {
            return true;
        }

        if(is_array($panel) && array_get($panel, 'type') === 'repeater') {
            return false;
        }

        return is_array($panel) &&
               array_key_exists('fields', $panel) &&
               ! empty($panel['fields']);
    }

    public static function fromArray($rawField)
    {
        $inst = (new static)->newFromManager($rawField);

        $inst->fields = collect((array) $inst->fields);

        $inst->fields = $inst->fields
            ->reject(function($field) {
                return ! Field::isValidField($field) &&
                       ! static::isValidPanel($field);
            })
            ->transform(function($field) {
            if(static::isValidPanel($field)) {
                return static::fromArray($field);
            }

            return Field::fromArray($field);
        });

        return $inst;
    }

    public function flatFields()
    {
        $fields = collect([]);

        foreach ($this->fields as $field) {
            if($field instanceof Panel) {
                $fields->merge($field->flatFields());
            } elseif ($field instanceof Field) {
                $fields->push($field);
            }
        }

        return $fields;
    }

    public function fieldsFor($view)
    {
        return $this->fields
            ->reject(function($field) use ($view) {
                return ! $field->isVisibleOn($view);
            })
            ->values();
    }

}