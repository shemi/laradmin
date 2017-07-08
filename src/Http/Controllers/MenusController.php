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
        parent::__construct();
    }

    public function index()
    {
        $menus = Menu::all();

        return view('laradmin::menus.browse', compact('menus'));
    }

    public function create()
    {
        $menu = new Menu;
        $routes = $this->getAllRoutes();

        return view('laradmin::menus.createEdit', compact('menu', 'routes'));
    }

    protected function getAllRoutes()
    {
        $routes = collect(\Route::getRoutes()->getRoutes());

        $routes = $routes->reject(function ($route) {
            return (! isset($route->action['as']) ||
                   (! in_array('GET', $route->methods)));
        })->transform(function ($route) {
            return [
                'name' => $route->action['as'],
                'action' => $route->action['controller'],
                'uri' => url($route->uri),
            ];
        })->values();

        return $routes;
    }

    public function getAllIcons()
    {
        $icons = DataManager::location('defaults')->load('md-icons');

        return $this->response(['icons' => $icons->icons->all()]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [

        ]);
    }

}