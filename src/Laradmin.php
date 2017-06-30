<?php

namespace Shemi\Laradmin;

use Illuminate\Contracts\Filesystem\Filesystem;
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
    }

    public function routes()
    {
        Route::group(['as' => 'laradmin.'], function() {
            require __DIR__.'/../routes/laradmin.php';
        });
    }

}