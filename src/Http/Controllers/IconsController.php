<?php

namespace Shemi\Laradmin\Http\Controllers;

use Shemi\Laradmin\Data\DataManager;

class IconsController extends Controller
{

    public function index()
    {
        $icons = DataManager::location('defaults')->load('fa-icons');

        return $this->response(['icons' => $icons]);
    }

}