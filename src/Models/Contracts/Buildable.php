<?php

namespace Shemi\Laradmin\Models\Contracts;

use Closure;

interface Buildable
{

    public function isInBuilderMode();

    public function builderMode(Closure $callback);

    public function toBuilder();

}