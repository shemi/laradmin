<?php

namespace Shemi\Laradmin\Models;

use Closure;
use Illuminate\Support\Collection;
use Shemi\Laradmin\Data\Model;
use Shemi\Laradmin\Transformers\Builder\SettingsPageTransformer;

/**
 * Shemi\Laradmin\Models\Type
 *
 * @property integer $id
 * @property string $name
 * @property string $slug
 * @property string $bucket
 * @property Collection $panels
 * @property Collection $fields
 * @property Collection $side_panels
 * @property Collection $main_panels
 */

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
        'icon',
        'bucket'
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

    public function getBucketAttribute($value = null)
    {
        return $value ?: $this->slug;
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

    public function getRelationData()
    {
        $data = [];

        $fields = static::extractAllFields($this->fields);

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

            if (! isset($field->relationship['model']) && ! $field->has_relationship_type) {
                continue;
            }

            if($field->has_relationship_type) {
                $relation = app($field->relationship_type->model);
            } else {
                $relation = app($field->relationship['model']);
            }

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
                'updated_at' => $page->updated_at,
                'created_at' => $page->created_at,
                'panels_count' => $page->panels->count(),
                'fields_count' => $page->fields->count()
            ];
        });

        return $all;
    }

    /**
     * @return mixed
     */
    public function toBuilderArray()
    {
        return SettingsPageTransformer::transform($this);
    }

    public function refresh()
    {
        $this->_panels = null;
        $this->_fields = null;

        return parent::refresh();
    }

}