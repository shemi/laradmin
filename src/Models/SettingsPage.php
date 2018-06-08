<?php

namespace Shemi\Laradmin\Models;

use Closure;
use Illuminate\Support\Collection;
use Shemi\Laradmin\Data\Model;

class SettingsPage extends Model
{

    protected $location = "options";

    protected $_fields;

    protected $_panels;

    protected $fillable = [
        'name',
        'slug',
        'public',
        'panels',
        'icon'
    ];

    /**
     * @param $slug
     * @return null|static
     */
    public static function whereSlug($slug)
    {
        return static::where('slug', $slug)->first();
    }

    public function getPanelsAttribute($value)
    {
        if($this->_panels instanceof Collection) {
            return $this->_panels;
        }

        $this->_panels = collect($value);

        $this->_panels->transform(function($panelArray) {
            return Panel::fromArray($panelArray, $this);
        });

        return $this->_panels;
    }

    public function getFieldsAttribute()
    {
        if($this->_fields instanceof Collection) {
            return collect($this->_fields);
        }

        $this->_fields = collect([]);

        foreach ($this->panels as $panel) {
            $this->_fields = $this->_fields->merge($panel->flatFields());
        }

        return collect($this->_fields);
    }

    public static function extractAllFields(Collection $fields, Closure $callback = null)
    {
        $newFields = collect([]);

        /** @var Field $field */
        foreach ($fields as $field) {
            if(! $callback || $callback($field)) {
                $newFields->push($field);
            }

            if($field->is_support_sub_fields) {
                $newFields = $newFields->merge(
                    static::extractAllFields($field->getSubFields(), $callback)
                );
            }
        }

        return $newFields;
    }

    public static function extractBrowseColumns(Collection $fields)
    {
        return static::extractAllFields($fields, function(Field $field) {
            return $field->isVisibleOn('browse');
        });
    }

    public function getSidePanelsAttribute()
    {
        return $this->panels
            ->reject(function($field) {
                return $field->position !== 'side';
            })
            ->values();
    }

    public function getMainPanelsAttribute()
    {
        return $this->panels
            ->reject(function($field) {
                return $field->position !== 'main';
            })
            ->values();
    }

    public function getModelArray(EloquentModel $model)
    {
        $array = [];

        /** @var Field $field */
        foreach ($this->fields as $field) {
            $array[$field->key] = $field->getModelValue($model);
        }

        return $array;
    }

    public function getRelationData(EloquentModel $model)
    {
        $data = [];

        $fields = $model->exists ? $this->edit_fields : $this->create_fields;

        /** @var Field $field */
        foreach ($fields as $field) {
            if(! $field->is_relationship || $field->is_ajax_powered_relationship) {
                continue;
            }

            if($field->type === 'repeater' && $field->has_relationship_type) {
                $type = $field->relationship_type;

                $data[$field->key] = $type->getRelationData(app($type->model));
                continue;
            }

            $data[$field->key] = [];
            $relation = $field->getRelationModelClass($model);

            foreach ($relation->all() as $relationInst) {
                $data[$field->key][] = $field->transformRelationModel($relationInst);
            }
        }

        return $data;
    }

    public static function getAllFieldTypes($fields, $parent = "")
    {
        $fieldTypes = [];

        /** @var Field $field */
        foreach ($fields as $field) {
            $fieldTypes[$parent.$field->key] = $field->type;

            if($field->getSubFields()->isNotEmpty()) {
                $fieldTypes = array_merge(
                    $fieldTypes,
                    static::getAllFieldTypes($field->getSubFields(), "{$parent}{$field->key}.")
                );
            }
        }

        return $fieldTypes;
    }

    public static function browseAll()
    {
        /** @var Collection $allTypes */
        $all = static::all();

        $all->transform(function($page) {
            /** @var static $type */
            return [
                'id' => $page->id,
                'name' => $page->name,
                'slug' => $page->slug,
                'updated_at' => $type->updated_at,
                'created_at' => $type->created_at,
                'panels_count' => $type->panels->count(),
                'fields_count' => $type->fields->count()
            ];
        });

        return $all;
    }

    public function toBuilderArray()
    {
        $fields = [
            'name',
            'slug',
            'updated_at',
            'created_at',
            'icon',
            'exists'
        ];

        $array = [];

        foreach ($fields as $key) {
            $array[$key] = $this->$key;
        }

        if($this->exists) {
            $array['panels'] = $this->panels->map(function($panel) {
                return $panel->toBuilder();
            });
        } else {
            $array['panels'] = (array) [];
        }

        return $array;
    }

    public function refresh()
    {
        $this->_panels = null;
        $this->_fields = null;

        return parent::refresh();
    }

}