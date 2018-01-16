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

        return new static(static::loadSchema());
    }

    public static function loadSchema()
    {
        if(! static::$filesystem->exists(static::getSchemaPath())) {
            return false;
        }

        return json_decode(
            static::$filesystem->get(static::getSchemaPath()),
            true
        );
    }

    public function fresh()
    {
        $this->items = static::loadSchema();

        return $this;
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

    public function increment($table)
    {
        $nextId = $this->getNextId($table);

        $this->incrementLastPrimary($table);

        return $nextId;
    }

    public function decrement($table)
    {
        $currentId = $this->getLastPrimary($table);

        if($currentId > 0) {
            $this->setLastPrimary($table, $currentId - 1)
                ->save();

            return $currentId - 1;
        }

        return $currentId;
    }

    public function get($key, $default = null)
    {
        return Arr::get($this->items, $key, $default);
    }

    public function getNextId($table)
    {
        return $this->getLastPrimary($table) + 1;
    }

    public function getLastPrimary($table)
    {
        return $this->get("tables.{$table}.last_primary", 0);
    }

    public function setLastPrimary($table, $id)
    {
        return $this->set("tables.{$table}.last_primary", $id);
    }

    public function incrementLastPrimary($table)
    {
        $this->setLastPrimary($table, $this->getNextId($table))
            ->save();

        return $this;
    }

    public function save()
    {
        return static::$filesystem->put(
            static::getSchemaPath(),
            $this->toJson(JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
    }

}