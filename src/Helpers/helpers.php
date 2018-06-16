<?php

use Illuminate\Support\Str;

if (! function_exists('laradmin_asset')) {
    function laradmin_asset($path, $secure = null)
    {
        return asset(config('laradmin.assets_path').'/'.$path, $secure);
    }
}

if (! function_exists('menu')) {
    function menu($slug)
    {
        return \Shemi\Laradmin\Models\Menu::whereSlug($slug);
    }
}

if (! function_exists('option')) {
    function option($key = null)
    {
        $manager = app('laradmin')->manager('options');

        return $key ? $manager->get($key) : $manager;
    }
}

if (! function_exists('laradmin')) {
    /**
     * @param $manager
     * @return \Illuminate\Foundation\Application|mixed|\Shemi\Laradmin\Contracts\Managers\ManagerContract|\Shemi\Laradmin\Laradmin
     * @throws \Shemi\Laradmin\Exceptions\ManagerDoesNotExistsException
     */
    function laradmin($manager = null)
    {
        if(! $manager) {
            return app('laradmin');
        }

        return app('laradmin')->manager($manager);
    }
}

function la_str_slug($title, $separator = '-')
{
    $slugger = new \Easybook\SeoUtf8Slugger($separator);

    return $slugger->slugify($title);
}

if (! function_exists('attr_json_encode')) {

    function attr_json_encode($value)
    {
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
    }

}