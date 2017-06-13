<?php

namespace Shemi\Laradmin;

use League\Flysystem\Filesystem;
use Route;

class Laradmin
{
    /**
     * @var \Illuminate\Foundation\Application|mixed
     */
    public $filesystem;

    public function __construct()
    {
        $this->filesystem = app(Filesystem::class);
        dd($this->filesystem);
    }

    protected function install()
    {

    }

    public function routes()
    {
        Route::group(['as' => 'laradmin.'], function() {
            require __DIR__.'../routes/laradmin.php';
        });
    }

}