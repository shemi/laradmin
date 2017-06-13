<?php

namespace Shemi\Laradmin\Managers;

use Illuminate\Database\Eloquent\Model;

class DataManager
{
    protected $model;

    protected $rows;

    public function __construct(Model $model)
    {
        $this->model = $model;

        $this->loadRows();
    }

    protected function loadDataFile()
    {

    }

    protected function loadRows()
    {

    }

    public function saveDataFile()
    {

    }



}