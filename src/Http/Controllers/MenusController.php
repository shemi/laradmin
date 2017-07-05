<?php

namespace Shemi\Laradmin\Http\Controllers;

use Illuminate\Http\Request;
use Shemi\Laradmin\Data\Data;
use Shemi\Laradmin\Data\DataManager;
use Shemi\Laradmin\Facades\Laradmin;
use Shemi\Laradmin\Models\Menu;

class MenusController extends Controller
{
    protected $menu;

    public function __construct()
    {
        $this->menu = Menu::where('location', 'admin');

        dd($this->menu);

        parent::__construct();
    }

    public function index()
    {

    }

    public function getAllIcons()
    {
        $icons = Laradmin::data()->load('md-icons', 'defaults');

        return $this->response(['icons' => $icons->all()]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [

        ]);
    }

}