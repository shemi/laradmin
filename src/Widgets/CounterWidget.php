<?php

namespace Shemi\Laradmin\Widgets;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CounterWidget extends Widget
{
    protected $view = 'laradmin::widgets.counter';

    /**
     * Font awesome icon name (eg: users)
     *
     * @var null|string
     */
    public $icon = null;

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

    public static function start($typeSlug = null, $title = '', $icon = '', $color = '')
    {
        $inst = (new static($typeSlug));

        $inst->title = $title;
        $inst->icon = $icon;
        $inst->color = $color;

        return $inst;
    }

    /**
     * @return Collection|integer
     */
    public function query()
    {
        /** @var Model $model */
        $model = app($this->type->model);
        $query = $model->select([$model->getKeyName()]);

        return $query->count() ?: 0;
    }

    public function getData()
    {
        return [
            'count' => $this->query(),
            'title' => $this->title,
            'icon' => $this->icon,
            'color' => $this->color,
            'action' => route('laradmin.'. $this->typeSlug .'.index')
        ];
    }

    /**
     * return the width of the widget from 1 to 12
     *
     * @return integer
     */
    public function getSize()
    {
        return 3;
    }
}