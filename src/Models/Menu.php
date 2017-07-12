<?php

namespace Shemi\Laradmin\Models;

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

}