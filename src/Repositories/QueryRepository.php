<?php

namespace Shemi\Laradmin\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Shemi\Laradmin\Contracts\Repositories\QueryRepository as QueryRepositoryContract;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;
use Shemi\Laradmin\Transformers\Response\BrowseModelTransformer;

class QueryRepository implements QueryRepositoryContract
{

    const ORDER_BY_REQUEST_KEY = 'order_by';
    const ORDER_DIRECTION_REQUEST_KEY = 'order';

    const SEARCH_TERM_REQUEST_KEY = 'search';

    const FILTERS_REQUEST_KEY = 'filters';

    const CUSTOM_ORDER_TABLE_ALIAS = 'la_order';
    const CUSTOM_ORDER_KEY = 'la_order_field';

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
        $this->query->orderBy($key = $this->getOrderBy(), $this->getOrderDirection());

        if($key === static::CUSTOM_ORDER_KEY) {
            $this->query->orderBy(
                "{$this->model->getTable()}.{$this->model->getKeyName()}",
                $this->getOrderDirection()
            );
        }

        return $this;
    }

    protected function load()
    {
        $columns = [];

        /** @var Field $column */
        foreach ($this->type->browse_columns as $column) {
            $field = null;

            if ($column->is_relationship) {
                $field = $column;
            }

            if ($column->is_sub_field && $column->parent->is_relationship) {
                $field = $column->parent;
            }

            if($field) {
                $columns[$field->key] = function($query){};

                if($field->relation_order_key) {
                    $columns[$field->key] = function($query) use ($field) {
                        $query->orderBy($field->relation_order_key);
                    };
                }
            }
        }

        $this->type->browse_columns->first(function (Field $field) use ($columns) {
            if ($field->is_media) {
                $columns['media'] = function($query){};

                return true;
            }

            return false;
        });

        if(count(array_keys($columns)) > 0) {
            $this->query->with($columns);
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

        $results->getCollection()
            ->transform(function ($model) {
                return $this->transformModel($model);
            });

        return $results;
    }

    public function get()
    {
        return $this->query->get()
            ->transform(function ($model) {
                return $this->transformModel($model);
            });
    }

    /**
     * @param $model
     * @return array
     * @throws \Exception
     */
    protected function transformModel($model)
    {
        return (new BrowseModelTransformer)
            ->transform($this->type->browse_columns, $model);
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
        $tableAlias = static::CUSTOM_ORDER_TABLE_ALIAS;
        $keyAlias = static::CUSTOM_ORDER_KEY;

        $relationClass = $parent->getRelationClass($this->model);
        $relationKey = $relationClass->getExistenceCompareKey();
        $relationKey = explode('.', $relationKey);
        $relationTable = array_shift($relationKey);
        $parentForeignKey = implode('.', $relationKey);
        $relationKey = "{$tableAlias}.{$parentForeignKey}";
        $orderingKey = "la_order.{$field->key} AS {$keyAlias}";


        $this->query
            ->addSelect($orderingKey)
            ->join(
                DB::raw(
                    "(SELECT `{$parentForeignKey}`, MAX(`{$field->key}`) AS `{$field->key}` " .
                    "FROM `{$relationTable}` " .
                    "GROUP BY `{$parentForeignKey}`) {$tableAlias}"
                ),
                function(JoinClause $join) use ($relationClass, $relationKey, $tableAlias) {
                    $join->on($relationKey, '=', $relationClass->getQualifiedParentKeyName());
                },
                null, null, 'left');

        return $keyAlias;
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
