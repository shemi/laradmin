<?php

namespace Shemi\Laradmin\Http\Controllers;

use Illuminate\Http\Request;
use Shemi\Laradmin\Data\Data;
use Shemi\Laradmin\Data\DataManager;
use Shemi\Laradmin\Facades\Laradmin;
use Shemi\Laradmin\Models\Menu;

class IconsController extends Controller
{

    public function index()
    {
        $icons = DataManager::location('defaults')->load('md-icons');

        return $this->response(['icons' => $icons['icons']]);
    }

}