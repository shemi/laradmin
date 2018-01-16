<?php

namespace Shemi\Laradmin\Data;

use Illuminate\Contracts\Filesystem\Filesystem;
use Shemi\Laradmin\Exceptions\DataNotFoundException;
use Shemi\Laradmin\Facades\Laradmin;

class DataManager
{
    const BASE_DIR = "laradmin";

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Model
     */
    protected $model;

    protected $location;

    /**
     * @var Collection
     */
    protected $files;

    /**
     * @var Schema
     */
    protected $schema;

    public function __construct()
    {
        $this->filesystem = Laradmin::filesystem();
        $this->loadSchema();
    }

    /**
     * @param $location
     * @return static
     */
    public static function location($location)
    {
        $inst = new static();

        $inst->location = $location;

        return $inst;
    }

    public function setModel(Model $model)
    {
        $this->location = $model->getLocation();
        $this->model = $model;

        return $this;
    }

    public function path($name)
    {
        return
            static::BASE_DIR . DIRECTORY_SEPARATOR
            . $this->location . DIRECTORY_SEPARATOR
            . trim($name, DIRECTORY_SEPARATOR);
    }

    public function all()
    {
        return $this->dir();
    }

    public function firstOrCreate($attributes)
    {
        $files = $this->dir();

        foreach ($attributes as $key => $value) {
            $files = $files->where($key, $value);
        }

        if ($files->isEmpty()) {
            return $this->create($attributes);
        }

        return $files->first();
    }

    public function findOrCreate($name)
    {
        if (! $data = $this->load($name)) {
            $data = $this->create($name);
        }

        return $data;
    }

    public function findOrFail($name)
    {
        if (! $data = $this->find($name)) {
            throw new DataNotFoundException($name);
        }

        return $data;
    }

    public function find($id)
    {
        try {
            $file = $this->load($id);
        }

        catch (\Exception $exception) {
            return null;
        }

        return $this->maybeNewModel($file);
    }

    protected function maybeNewModel(array $data)
    {
        if ($this->model) {
            return $this->newModel($data);
        }

        return $data;
    }

    public function newModelInstance($attributes = [])
    {
        return $this->model->newInstance($attributes);
    }

    protected function newModel(array $data)
    {
        return $this->model->newFromManager($data);
    }

    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param $name
     * @param string $ext
     * @return mixed
     * @throws \Exception
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function load($name, $ext = 'json')
    {
        $path = $this->path($name . ($ext ? '.' . $ext : ''));

        if (! $this->filesystem->exists($path)) {
            throw new \Exception("\"$path\" cannot be found");
        }

        return json_decode($this->filesystem->get($path), true);
    }

    public function delete($name, $ext = 'json', $decrement = false)
    {
        $deleted = $this->filesystem
            ->delete(
                $this->path($name . ($ext ? '.' . $ext : ''))
            );

        if($deleted && $decrement) {
            $this->schema()->decrement($this->location);
        }

        return $deleted;
    }

    public function dir($path = null, $ext = 'json')
    {
        $path = $this->path($path);

        if (! $this->filesystem->exists($path)) {
            return $this->model->newCollection([]);
        }

        $names = $this->filesystem->allFiles($path);

        if (count($names) === 0) {
            return $this->model->newCollection([]);
        }

        $this->files = $this->model->newCollection([]);

        foreach ($names as $name) {
            $name = basename($name);

            if (! ends_with($name, '.' . $ext)) {
                continue;
            }

            $file = $this->load($name, null);

            if ($file) {
                $file = $this->maybeNewModel($file);
            }

            $this->files->add($file ?: []);
        }

        return $this->files;
    }

    public function create(array $attributes = [])
    {
        return tap($this->newModelInstance($attributes), function ($instance) {
            $instance->save();
        });
    }

    public function forceCreate(array $attributes)
    {
        return $this->model->unguarded(function () use ($attributes) {
            return $this->newModelInstance()->create($attributes);
        });
    }

    public function save(Model $model = null)
    {
        $model = $model ?: $this->model;

        if ($model->getIncrementing() && ! $model->exists) {
            $model->setAttribute(
                $model->getKeyName(),
                $this->schema()->increment($this->location)
            );
        }

        $id = $model->getKey();

        $json = $model->toJson(JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $path = $this->path($id . '.json');

        $this->filesystem->put($path, $json);

        return $id;
    }

    protected function loadSchema()
    {
        $this->schema = Schema::load($this->filesystem);

        return $this->schema;
    }

    /**
     * @return Schema
     */
    protected function schema()
    {
        if (! $this->schema) {
            $this->loadSchema();
        }

        return $this->schema;
    }

    public function __call($name, $arguments)
    {
        return $this->dir()->$name(...$arguments);
    }

    public static function __callStatic($name, $arguments)
    {
        return (new static)->$name(...$arguments);
    }

}