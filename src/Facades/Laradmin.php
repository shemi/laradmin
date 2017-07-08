<?php

namespace Shemi\Laradmin\Facades;

use Illuminate\Support\Facades\Facade;

class Laradmin extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laradmin';
    }

}