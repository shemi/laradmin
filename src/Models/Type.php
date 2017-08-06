<?php

namespace Shemi\Laradmin\Models;

use Illuminate\Routing\Exceptions\UrlGenerationException;
use InvalidArgumentException;
use Shemi\Laradmin\Data\Model;

class Menu extends Model
{
    protected $fillable = [
        'items',
        'name',
        'slug'
    ];

    public static function whereSlug($slug)
    {
        return static::where('slug', $slug)->first();
    }

    public function getItemsAttribute($key)
    {
        return $this->transformItems($key);
    }

    public function transformItems($items, $throw = false)
    {
        $newItems = [];

        foreach ($items as $item) {
            if ($newItem = $this->transformItem($item, $throw)) {
                $newItems[] = $newItem;
            }
        }

        return $newItems;
    }

    public function transformItem($item, $throw = false)
    {
        $type = array_get($item, 'type', 'url');
        $routeName = array_get($item, 'route_name', '');
        $url = array_get($item, 'url', "");
        $items = array_get($item, 'items', []);

        if ($type === 'route') {
            $routeParts = explode('|', $routeName);
            $routeName = array_shift($routeParts);
            $parameters = [];

            foreach ($routeParts as $part) {
                $part = explode(':', $part);

                if (count($part) != 2) {
                    continue;
                }

                $parameters[$part[0]] = $part[1];
            }

            try {
                $url = route($routeName, $parameters, false);
            } catch (UrlGenerationException | InvalidArgumentException $e) {
                if(! $throw) {
                    return false;
                } else {
                    throw $e;
                }
            }
        }

        $isActive = url()->current() === url()->to($url);

        if(! empty($items)) {
            $items = $this->transformItems($items, $throw);
        }

        return [
            'id' => array_get($item, 'id') ?: random_int(1, 10000),
            'title' => e(array_get($item, 'title', "")),
            'type' => $type,
            'route_name' => $routeName,
            'url' => $url,
            'in_new_window' => array_get($item, 'in_new_window', false),
            'icon' => array_get($item, 'icon', ""),
            'css_class' => e(array_get($item, 'css_class', "")),
            'items' => $items,
            'is_active' => $isActive
        ];
    }

}