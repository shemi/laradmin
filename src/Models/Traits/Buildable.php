<?php

namespace Shemi\Laradmin\Models\Traits;

use Closure;
use Shemi\Laradmin\Models\Field;

trait Buildable
{

    protected static $inBuilderMode = false;

    protected function startBuilderMode()
    {
        static::$inBuilderMode = true;
    }

    protected function endBuilderMode()
    {
        static::$inBuilderMode = false;
    }

    public function isInBuilderMode()
    {
        return static::$inBuilderMode;
    }

    public function builderMode(Closure $callback)
    {
        $this->startBuilderMode();

        $data = $callback();

        $this->endBuilderMode();

        return $data;
    }

    public function toArray()
    {
        return $this->builderMode(function() {
            return parent::toArray();
        });
    }

}