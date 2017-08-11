<?php

namespace Shemi\Laradmin\Models;

use Illuminate\Support\Collection;
use Shemi\Laradmin\Data\Model;

use \Illuminate\Database\Eloquent\Model as EloquentModel;

class Type extends Model
{
    protected $_fields;

    protected $_panels;

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

    public function getPanelsAttribute($value)
    {
        if($this->_panels instanceof Collection) {
            return $this->_panels;
        }

        $this->_panels = collect($value);

        $this->_panels->transform(function($panelArray) {
            return Panel::fromArray($panelArray);
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

    public function getEditFieldsAttribute()
    {
        $fields = $this->fields
            ->reject(function($field) {
                return ! $field->isVisibleOn('edit');
            })
            ->values();

        return $fields;
    }

    public function getCreateFieldsAttribute()
    {
        $fields = $this->fields
            ->reject(function($field) {
                return ! $field->isVisibleOn('create');
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
            ->sortBy('browse_order')
            ->values();

        return $fields;
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

    public function getRelationData(EloquentModel $model)
    {
        $data = [];

        $fields = $model->exists ? $this->edit_fields : $this->create_fields;

        foreach ($fields as $field) {
            if($field->is_relationship) {
                $data[$field->key] = [];
                $relation = $field->getRelationModelClass($model);

                foreach ($relation->all() as $relationInst) {
                    $data[$field->key][] = [
                        'key' => $relationInst->getAttribute($field->relationship['key']),
                        'label' => $relationInst->getAttribute($field->relationship['label'])
                    ];
                }
            }
        }

        return $data;
    }

}