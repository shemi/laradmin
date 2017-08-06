<?php

namespace Shemi\Laradmin\Models;

use Shemi\Laradmin\Data\Model;

class Type extends Model
{
    protected $fillable = [
        'name',
        'model',
        'slug',
        'public',
        'controller',
        'fields',
    ];

    public static function whereSlug($slug)
    {
        return static::where('slug', $slug)->first();
    }

}