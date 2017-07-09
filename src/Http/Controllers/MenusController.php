<?php

namespace Shemi\Laradmin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\UrlGenerationException;
use InvalidArgumentException;
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

    public function validateItem(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'type' => 'in:route,url',
            'route_name' => 'required_if:type,route',
            'url' => 'required_if:type,url',
        ]);

        $route_url = "";

        if($request->input('type') === 'route') {
            $routeParts = explode('|', $request->input('route_name'));
            $routeName = array_shift($routeParts);
            $parameters = [];

            foreach ($routeParts as $part) {
                $part = explode(':', $part);

                if(count($part) != 2) {
                    continue;
                }

                $parameters[$part[0]] = $part[1];
            }

            try {
                $route_url = route($routeName, $parameters);
            } catch (UrlGenerationException | InvalidArgumentException $e) {
                return $this->responseValidationError([
                    'route_name' => [$e->getMessage()]
                ]);
            }
        }

        return $this->response([
            'id' => random_int(1, 10000),
            'title' => e($request->input('title')),
            'type' => $request->input('type'),
            'route_name' => $request->input('route_name'),
            'url' => $request->input('url'),
            'in_new_window' => $request->input('in_new_window'),
            'icon' => $request->input('icon'),
            'css_class' => e($request->input('css_class')),
            'route_url' => $route_url,
            'items' => []
        ]);
    }

}