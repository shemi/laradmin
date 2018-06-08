<?php

namespace Shemi\Laradmin\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{

    protected $table = "la_settings";


    protected $fillable = [
        'key',
        'value',
        'type',
        'bucket'
    ];


    protected $casts = [
        'encrypted' => 'boolean',
        'value' => 'json'
    ];


}