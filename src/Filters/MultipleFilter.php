<?php

namespace Shemi\Laradmin\Filters;

use Shemi\Laradmin\Filters\Contracts\MultipleFilterContract;

abstract class MultipleFilter extends SearchableFilter implements MultipleFilterContract
{
    protected $view = "multiple";

}