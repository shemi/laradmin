<?php

namespace Shemi\Laradmin\Data;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class Schema extends Collection
{

    /**
     * @var Filesystem
     */
    protected static $filesystem;

    const BASE_DIR = "laradmin";

    public static function load(Filesystem $filesystem)
    {
        static::$filesystem = $filesystem;

        $schema = json_decode(
            static::$filesystem->get(static::getSchemaPath()),
            true
        );

        return new static($schema);
    }

    protected static function getSchemaPath()
    {
        return static::BASE_DIR.DIRECTORY_SEPARATOR.'schema.json';
    }

    public function set($key, $value)
    {
        Arr::set($this->items, $key, $value);

        return $this;
    }

    public function getAndIncrementNextId($for)
    {
        $nextId = $this->getNextId($for);

        $this->incrementLastPrimary($for);

        return $nextId;
    }

    public function get($key, $default = null)
    {
        return Arr::get($this->items, $key, $default);
    }

    public function getNextId($for)
    {
        return $this->get("tables.{$for}.last_primary", 0) + 1;
    }

    public function incrementLastPrimary($for)
    {
        $id = $this->getNextId($for);

        $this->set("tables.{$for}.last_primary", $id)
            ->save();

        return $this;
    }

    public function save()
    {
        return static::$filesystem->put(
            static::getSchemaPath(),
            $this->toJson()
        );
    }

}