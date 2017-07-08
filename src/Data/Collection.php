<?php

namespace Shemi\Laradmin\Data;

use \Illuminate\Support\Collection as BaseCollection;

class Collection extends BaseCollection
{

    public function add($model)
    {
        $this->items[] = $model;

        return $this;
    }

}