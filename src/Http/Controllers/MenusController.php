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

    public function edit($menu, Request $request)
    {
        $menu = Menu::whereSlug($menu);
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
            'name' => 'required',
            'items' => 'array'
        ]);

        $menu = Menu::create([
            'name' => $request->name,
            'items' => $request->items,
            'slug' => str_slug($request->name)
        ]);

        return $this->response([
            'menu' => $menu->toJson(),
            'redirect' => route('laradmin.menus.menus.edit', [
                'menu' => $menu->slug
            ])
        ]);
    }

    public function update($menu, Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'items' => 'array'
        ]);

        $menu = Menu::findOrFail($menu);

        $redirect = $menu->name != $request->input('name');

        $menu->name = $request->input('name');
        $menu->slug = str_slug($request->input('name'));
        $menu->items = $request->input('items');
        $menu->save();

        return $this->response([
            'menu' => $menu->toJson(),
            'redirect' => $redirect ?
                route('laradmin.menus.menus.edit', ['menu' => $menu->slug]) :
                false
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

        try {
            $item = (new Menu)->transformItem(
                $request->only([
                    'id', 'type', 'title',
                    'route_name', 'url',
                    'in_new_window', 'icon',
                    'css_class', 'items',
                ]),
                true
            );
        } catch (UrlGenerationException | InvalidArgumentException $e) {
            return $this->responseValidationError([
                'route_name' => [$e->getMessage()]
            ]);
        }

        return $this->response($item);
    }

    public function destroy($menuId, Request $request)
    {
        $menu = Menu::findOrFail($menuId);
        $action = $menu->delete();

        return $this->response([
            'action' => $action,
            'redirect' => route('laradmin.menus.menus.index', [], false)
        ]);
    }

}