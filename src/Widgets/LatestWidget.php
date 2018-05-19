<?php

namespace Shemi\Laradmin\Widgets;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Shemi\Laradmin\Facades\Laradmin;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Repositories\QueryRepository;

class LatestWidget extends Widget
{
    protected $view = 'laradmin::widgets.latest';

    public $items = 5;

    public $exclude = [];

    /**
     * The widget title before the count
     *
     * @var null|string
     */
    public $title = null;

    /**
     * The widget BG color
     *
     * @var null|string
     */
    public $color = null;

    public static function start($typeSlug = null, $title = '', $exclude = [])
    {
        $inst = (new static($typeSlug));

        $inst->title = $title;
        $inst->exclude = $exclude;

        return $inst;
    }

    /**
     * @return Collection|integer
     */
    public function query()
    {
        $model = app($this->type->model);

        return QueryRepository::customQuery($this->type, function(QueryRepository $repository) {
            $repository->query->latest()->take($this->items);
        })
        ->transform(function($item) use ($model) {
            $item['la_edit_link'] = Laradmin::manager('links')->edit($this->type, $item[$model->getKeyName()]);

            return $item;
        });
    }

    public function getData()
    {
        return [
            'data' => $this->query(),
            'fields' => $this->getBrowseColumnsWithoutImages(),
            'image' => $this->getImageColumn(),
            'title' => $this->title ?: trans('laradmin::widgets.latest.title', ['name' => $this->type->name]),
            'all' => route('laradmin.'. $this->typeSlug .'.index')
        ];
    }

    protected function getBrowseColumns()
    {
        return $this->type->browse_columns->reject(function(Field $field) {
            return in_array($field->key, $this->exclude);
        });
    }

    protected function getBrowseColumnsWithoutImages()
    {
        return $this->getBrowseColumns()->reject(function(Field $field) {
            return in_array($field->type, ['image', 'images']);
        });
    }

    protected function getImageColumn()
    {
        return $this->getBrowseColumns()->first(function(Field $field) {
            return in_array($field->type, ['image']);
        });
    }

    /**
     * return the width of the widget from 1 to 12
     *
     * @return integer
     */
    public function getSize()
    {
        return 6;
    }
}