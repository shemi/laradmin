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
    protected $query;

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

    public function __construct(Request $request, Type $type)
    {
        $this->request = $request;
        $this->type = $type;
        $this->model = app($type->model);
        $this->query = $this->model::select('*');
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

    protected function filter()
    {
        if($this->type->filterable_fields->isEmpty() || ! $this->hasFilters()) {
            return $this;
        }

        $this->query->where(function($query) {
            /** @var Field $field */
            foreach ($this->type->filterable_fields as $index => $field) {
                $filterKeys = $this->getFilter($field->key);

                if(empty($filterKeys)) {
                    continue;
                }

                $relationModel = $field->getRelationModelClass($this->model);

                $query->whereHas($field->key, function($query) use ($field, $filterKeys, $relationModel) {
                    $query->whereIn($relationModel->getKeyName(), $filterKeys);
                });
            }
        });

        return $this;
    }

    protected function search()
    {
        $term = $this->getSearchTerm();

        if(empty($term)) {
            return $this;
        }

        /** @var Field $field */
        foreach ($this->type->searchable_fields as $field) {
            if($field->is_relationship) {
                $this->whereHasFields->push($field);
            } else {
                $this->whereFields->push($field);
            }
        }

        $this->query->where(function($query) {
            $this->addWhere($query)
                ->addWhereHas($query);
        });

        return $this;
    }

    protected function addWhere($query)
    {
        if($this->whereFields->isEmpty()) {
            return $this;
        }

        $query->where(function($query) {
            foreach ($this->whereFields as $index => $field) {
                $term = $this->getSearchTerm();
                $term = $field->search_comparison === 'like' ? "%{$term}%" : $term;
                $method = $index === 0 ? 'where' : 'orWhere';

                $query->{$method}($field->key, $field->search_comparison, $term);
            }
        });

        return $this;
    }

    protected function addWhereHas($query)
    {
        if($this->whereHasFields->isEmpty()) {
            return $this;
        }

        $method = $this->whereFields->isEmpty() ? 'where' : 'orWhere';

        $query->{$method}(function($query) {
            /** @var Field $field */
            foreach ($this->whereHasFields as $index => $field) {
                $term = $this->getSearchTerm();
                $term = $field->search_comparison === 'like' ? "%{$term}%" : $term;
                $method = $index === 0 ? 'whereHas' : 'orWhereHas';

                $query->{$method}($field->key, function($query) use ($field, $term) {
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
        $columns = $this->type->browse_columns
            ->where('is_relationship', '===', true)
            ->pluck('key');

        if($columns->isNotEmpty()) {
            $this->query->with($columns->toArray());
        }

        return $this;
    }

    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    protected function paginate()
    {
        $results = $this->query
            ->paginate($this->type->records_per_page);

        $hasPrimaryKeyField = (bool) $this->type->browse_columns
            ->where('key', $this->primaryKey)
            ->count();

        $results->getCollection()
            ->transform(function ($model) use ($hasPrimaryKeyField) {
                $return = [];

                foreach ($this->type->browse_columns as $column) {
                    $return[$column->key] = $column->getBrowseValue($model);
                }

                if(! $hasPrimaryKeyField) {
                    $return[$this->primaryKey] = $model->getKey();
                }

                return $return;
            });

        return $results;
    }

    protected function getFilter($for = null)
    {
        $key = $for ? static::FILTERS_REQUEST_KEY.'.'.$for : static::FILTERS_REQUEST_KEY;

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

        return $orderBy;
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

}
