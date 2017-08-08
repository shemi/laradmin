<?php

namespace Shemi\Laradmin\Models;

use Shemi\Laradmin\Data\Model;

use \Illuminate\Database\Eloquent\Model as EloquentModel;

class Panel extends Model
{
    protected $dataable = false;

    protected $fillable = [
        'id',
        'title',
        'position',
        'is_main_meta',
        'fields'
    ];



}