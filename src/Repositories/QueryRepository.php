<?php

namespace Shemi\Laradmin\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Shemi\Laradmin\Contracts\Repositories\QueryRepository as QueryRepositoryContract;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;

class QueryRepository implements QueryRepositoryContract
{

    const ORDER_BY_REQUEST_KEY = 'order_by';
    const ORDER_DIRECTION_REQUEST_KEY = 'order';

    const SEARCH_TERM_REQUEST_KEY = 'search';

    const FILTERS_REQUEST_KEY = 'filters';

    /**
     * @var Type
     */
    protected $type;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var Builder
     */
    public $query;

    /**
     * @var string
     */
    protected $primaryKey;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $whereFields;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $whereHasFields;

    public function __construct(Request $request = null, Type $type = null)
    {
        $this->request = $request;
        $this->type = $type;
        $this->model = app($type->model);
        $this->query = $this->model::select($this->model->getTable() . '.*');
        $this->primaryKey = $this->model->getKeyName();
        $this->whereFields = collect([]);
        $this->whereHasFields = collect([]);
    }

    /**
     * @param Request $request
     * @param Type $type
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function query(Request $request, Type $type)
    {
        return (new static($request, $type))
            ->filter()
            ->search()
            ->order()
            ->load()
            ->paginate();
    }

    public static function customQuery(Type $type, callable $callback)
    {
        $inst = (new static(null, $type));

        $callback($inst);

        $inst->load();

        return $inst->get();
    }

    protected function filter()
    {
        if ($this->type->filterable_fields->isEmpty() || ! $this->hasFilters()) {
            return $this;
        }

        $this->query->where(function ($query) {
            /** @var Field $field */
            foreach ($this->type->filterable_fields as $index => $field) {
                $filterKeys = $this->getFilter($field->key);

                if (empty($filterKeys)) {
                    continue;
                }

                $relationModel = $field->getRelationModelClass($this->model);

                $query->whereHas($field->key, function ($query) use ($field, $filterKeys, $relationModel) {
                    $query->whereIn($relationModel->getKeyName(), $filterKeys);
                });
            }
        });

        return $this;
    }

    protected function search()
    {
        $term = $this->getSearchTerm();

        if (empty($term)) {
            return $this;
        }

        /** @var Field $field */
        foreach ($this->type->searchable_fields as $field) {
            if ($field->is_relationship) {
                $this->whereHasFields->push($field);
            } elseif ($field->is_sub_field && $field->parent->is_relationship) {
                $this->whereHasFields->push($field->parent);
            } else {
                $this->whereFields->push($field);
            }
        }

        $this->query->where(function ($query) {
            $this->addWhere($query)
                ->addWhereHas($query);
        });

        return $this;
    }

    protected function addWhere($query)
    {
        if ($this->whereFields->isEmpty()) {
            return $this;
        }

        $query->where(function ($query) {
            /** @var Field $field */
            foreach ($this->whereFields as $index => $field) {
                $term = $this->getSearchTerm();
                $term = $field->search_comparison === 'like' ? "%{$term}%" : $term;
                $method = $index === 0 ? 'where' : 'orWhere';
                $key = $field->browse_key;

                if ($field->is_sub_field) {
                    $key = $this->transformJsonKey($key);
                }

                $query->{$method}($key, $field->search_comparison, $term);
            }
        });

        return $this;
    }

    protected function addWhereHas($query)
    {
        if ($this->whereHasFields->isEmpty()) {
            return $this;
        }

        $method = $this->whereFields->isEmpty() ? 'where' : 'orWhere';

        $query->{$method}(function ($query) {
            /** @var Field $field */
            foreach ($this->whereHasFields as $index => $field) {
                $term = $this->getSearchTerm();
                $term = $field->search_comparison === 'like' ? "%{$term}%" : $term;
                $method = $index === 0 ? 'whereHas' : 'orWhereHas';

                $query->{$method}($field->key, function ($query) use ($field, $term) {
                    foreach ($field->relation_labels as $index => $label) {
                        $method = $index === 0 ? 'where' : 'orWhere';

                        $query->{$method}($label, $field->search_comparison, $term);
                    }
                });
            }
        });

        return $this;
    }

    protected function order()
    {
        $this->query->orderBy($this->getOrderBy(), $this->getOrderDirection());

        return $this;
    }

    protected function load()
    {
        $columns = collect([]);

        /** @var Field $column */
        foreach ($this->type->browse_columns as $column) {
            if ($column->is_relationship) {
                $columns->push($column->key);
            }

            if ($column->is_sub_field && $column->parent->is_relationship) {
                $columns->push($column->parent->key);
            }
        }

        $this->type->browse_columns->first(function (Field $field) use ($columns) {
            if ($field->is_media) {
                $columns->push('media');

                return true;
            }

            return false;
        });

        if ($columns->isNotEmpty()) {
            $this->query->with($columns->unique()->toArray());
        }

        return $this;
    }

    protected function hasPrimaryKeyField()
    {
        return (bool) $this->type->browse_columns
            ->where('key', $this->primaryKey)
            ->count();
    }

    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    protected function paginate()
    {
        $results = $this->query
            ->paginate($this->type->records_per_page);

        $hasPrimaryKeyField = $this->hasPrimaryKeyField();

        $results->getCollection()
            ->transform(function ($model) use ($hasPrimaryKeyField) {
                return $this->transformModel($model, $hasPrimaryKeyField);
            });

        return $results;
    }

    public function get()
    {
        $hasPrimaryKeyField = $this->hasPrimaryKeyField();

        return $this->query->get()
            ->transform(function ($model) use ($hasPrimaryKeyField) {
                return $this->transformModel($model, $hasPrimaryKeyField);
            });
    }

    protected function transformModel($model, $hasPrimaryKeyField)
    {
        $return = [];

        /** @var Field $column */
        foreach ($this->type->browse_columns as $column) {
            array_set($return, $column->browse_key, $column->getBrowseValue($model));
        }

        if (! $hasPrimaryKeyField) {
            $return[$this->primaryKey] = $model->getKey();
        }

        return $return;
    }

    protected function getFilter($for = null)
    {
        $key = $for ? static::FILTERS_REQUEST_KEY . '.' . $for : static::FILTERS_REQUEST_KEY;

        return array_filter(array_values((array) $this->request->input($key, [])));
    }

    protected function hasFilters()
    {
        return ! empty($this->getFilter());
    }

    protected function getOrderBy()
    {
        $orderBy = $this->request->input(static::ORDER_BY_REQUEST_KEY);

        if (! $orderBy) {
            $orderBy = $this->type->default_sort;
        }

        if (str_contains($orderBy, '.')) {
            /** @var Field|null $field */
            $field = $this->type->browse_columns->first(function (Field $field) use ($orderBy) {
                return $field->browse_key === $orderBy;
            });

            if ($field && $field->is_sub_field && $field->parent->is_relationship) {
                return $this->addOrderJoin($field, $field->parent);
            }
        }

        return $this->transformJsonKey($orderBy);
    }

    protected function addOrderJoin(Field $field, Field $parent)
    {
        $relationClass = $parent->getRelationClass($this->model);
        $relationKey = $relationClass->getExistenceCompareKey();
        $relationKey = explode('.', $relationKey);
        $relationTable = array_shift($relationKey);
        $relationKey = "la_order.".implode('.', $relationKey);
        $orderingKey = "la_order.{$field->key} AS la_order_field";


        $this->query
            ->addSelect($orderingKey)
            ->leftJoin(
                $relationTable . ' AS la_order',
                $relationKey,
                $relationClass->getQualifiedParentKeyName()
            );

        return "la_order_field";
    }

    protected function getOrderDirection()
    {
        return $this->request->input(
            static::ORDER_DIRECTION_REQUEST_KEY,
            $this->type->default_sort_direction
        );
    }

    protected function getSearchTerm()
    {
        return trim(
            $this->request->input(static::SEARCH_TERM_REQUEST_KEY, '')
        );
    }

    protected function transformJsonKey($key)
    {
        return str_replace('.', '->', $key);
    }

    public function fresh()
    {
        return new static;
    }

}
