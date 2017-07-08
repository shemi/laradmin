<?php

namespace Shemi\Laradmin\Models;

use Shemi\Laradmin\Data\Model;

class Menu extends Model
{
    protected $fillable = [
        'items',
        'location'
    ];

    protected $casts = [
        'items' => 'array'
    ];



}