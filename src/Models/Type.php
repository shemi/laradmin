<?php

namespace Shemi\Laradmin\Models;

use Carbon\Carbon;
use Closure;
use Illuminate\Support\Collection;
use Shemi\Laradmin\Data\Model;

use \Illuminate\Database\Eloquent\Model as EloquentModel;
use Shemi\Laradmin\Http\Controllers\CrudController;
use Shemi\Laradmin\Http\Controllers\ExportController;
use Shemi\Laradmin\Http\Controllers\ImportController;
use Shemi\Laradmin\Models\Contracts\Buildable as BuildableContract;
use Shemi\Laradmin\Models\Traits\Buildable;
use Shemi\Laradmin\Transformers\Builder\TypeTransformer;

/**
 * Shemi\Laradmin\Models\Type
 *
 * @property integer $id
 * @property string $name
 * @property string $model
 * @property string $slug
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property boolean $public
 * @property string $controller
 * @property Collection $panels
 * @property integer $records_per_page
 * @property Collection $fields
 * @property Collection $browse_columns
 * @property Collection $edit_fields
 * @property Collection $create_fields
 * @property Collection $searchable_fields
 * @property Collection $filterable_fields
 * @property Collection $side_panels
 * @property Collection $main_panels
 * @property Collection $filters
 * @property boolean $support_export
 * @property string $export_controller
 * @property boolean $support_import
 * @property string $import_controller
 * @property string $default_sort
 * @property string $default_sort_direction
 */
class Type extends Model implements BuildableContract
{

    use Buildable;

    protected $_fields;

    protected $_panels;

    protected $fillable = [
        'name',
        'model',
        'slug',
        'public',
        'controller',
        'panels',
        'icon',
        'records_per_page',
        'default_sort',
        'default_sort_direction',
        'filters'
    ];

    /**
     * @param $slug
     * @return null|static
     */
    public static function whereSlug($slug)
    {
        return static::where('slug', $slug)->first();
    }

    public function hasModel()
    {
        return ! empty($this->model);
    }

    public function getControllerAttribute($value)
    {
        if(is_null($value)) {
            return CrudController::class;
        }

        return $value;
    }

    public function getDefaultSortAttribute($value)
    {
        if(is_null($value)) {
            $field = $this->browse_columns
                ->where('sortable', '===', true)
                ->first();

            if($field) {
                return $field->key;
            }

            return $this->model ? app($this->model)->getKeyName() : null;
        }

        return $value;
    }

    public function getDefaultSortDirectionAttribute($value)
    {
        if(is_null($value)) {
            return 'DESC';
        }

        return $value;
    }

    public function getExportControllerAttribute($value)
    {
        if(is_null($value)) {
            return ExportController::class;
        }

        return $value;
    }

    public function getImportControllerAttribute($value)
    {
        if(is_null($value)) {
            return ImportController::class;
        }

        return $value;
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

    public function getBrowseColumnsAttribute()
    {
        return static::extractBrowseColumns(collect($this->fields))
            ->sortBy('browse_order')
            ->values();
    }

    public function getEditFieldsAttribute()
    {
        return $this->fields
            ->reject(function($field) {
                return ! $field->isVisibleOn('edit');
            })
            ->values();
    }

    public function getCreateFieldsAttribute()
    {
        return $this->fields
            ->reject(function($field) {
                return ! $field->isVisibleOn('create');
            })
            ->values();
    }

    public function getSearchableFieldsAttribute()
    {
        return static::extractAllFields(collect($this->fields), function(Field $field) {
            return $field->searchable;
        })
        ->sortBy('browse_order')
        ->values();
    }

    public function getFilterableFieldsAttribute()
    {
        return static::extractAllFields(collect($this->fields), function(Field $field) {
            return $field->filterable;
        })
        ->sortBy('browse_order')
        ->values();
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

    /**
     * @param EloquentModel $model
     * @return array
     * @throws \Exception
     */
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

            if($field->is_repeater_like && $field->has_relationship_type) {
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
        $allTypes = static::all();

        $allTypes->transform(function($type) {
            /** @var static $type */
            return [
                'id' => $type->id,
                'name' => $type->name,
                'slug' => $type->slug,
                'model' => $type->model,
                'controller' => $type->controller,
                'updated_at' => $type->updated_at,
                'created_at' => $type->created_at,
                'records_per_page' => $type->records_per_page,
                'panels_count' => $type->panels->count(),
                'fields_count' => $type->fields->count()
            ];
        });

        return $allTypes;
    }

    public function filters()
    {
        if(! is_array($this->filters)) {
            $this->filters = [];
        }

        $filters = collect([]);

        foreach ($this->filters as $filter) {
            if(empty($filter)) {
                continue;
            }

            if(app('laradmin')->filters()->has($filter)) {
                $filters->push(app('laradmin')->filters()->get($filter));
            }
        }

        return $filters;
    }

    public function toBuilder()
    {
        return $this->builderMode(function () {
            return TypeTransformer::transform($this);
        });
    }

    public function refresh()
    {
        $this->_panels = null;
        $this->_fields = null;

        return parent::refresh();
    }

}