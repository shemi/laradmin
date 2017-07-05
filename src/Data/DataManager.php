<?php

namespace Shemi\Laradmin\Data;

use Illuminate\Contracts\Filesystem\Filesystem;
use Shemi\Laradmin\Facades\Laradmin;
use Shemi\Laradmin\Models\Model;

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

    protected $files = [];

    public function __construct()
    {
        $this->filesystem = Laradmin::filesystem();
    }

    public function location($location)
    {
        $this->location = $location;

        return $this;
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

    public function find($name)
    {
        $data = $this->maybeNewModel($this->load($name));

        return $data;
    }

    public function get()
    {

    }

    protected function maybeNewModel(array $data)
    {
        if($this->model) {
            return $this->newModel($data);
        }

        return $data;
    }

    protected function newModel(array $data)
    {
        return $this->model->newFromManager($data);
    }

    public function load($name)
    {
        $path = $this->path($name) . '.json';

        if (! $this->filesystem->exists($path)) {
            return false;
        }

        return json_decode($this->filesystem->get($path), true);
    }

    public function dir($path = null)
    {
        $path = $this->path($path);

        if(! $this->filesystem->exists($path)) {
            return collect([]);
        }

        $names = $this->filesystem->allFiles($path);

        if(count($names) === 0) {
            return collect([]);
        }

        $files = collect([]);

        foreach ($names as $name) {
            $name = explode('.', basename($name));
            array_pop($name);
            $name = implode('.', $name);

            $file = $this->load($name);


            if($file) {
                $file = $this->maybeNewModel($file);
            }

            $files->push($file ?: []);
        }

        return $files;
    }

    public function create($name, $location = '', $data = [])
    {
        return new Data($name, $location, $data);
    }

    public function save(Model $data)
    {
        $json = $data->toJson(JSON_UNESCAPED_UNICODE);
        $path = $this->path($data->getName(), $data->getLocation()) . '.json';

        return $this->filesystem->put($path, $json);
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