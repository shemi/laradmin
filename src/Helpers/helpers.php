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

function la_str_slug($title, $separator = '-')
{
    $slugger = new \Easybook\SeoUtf8Slugger($separator);

    return $slugger->slugify($title);
}