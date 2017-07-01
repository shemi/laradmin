<?php

if (! function_exists('laradmin_asset')) {
    function laradmin_asset($path, $secure = null)
    {
        return asset(config('laradmin.assets_path').'/'.$path, $secure);
    }
}