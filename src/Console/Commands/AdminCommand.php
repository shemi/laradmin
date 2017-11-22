<?php

namespace Shemi\Laradmin\Console\Commands;

use Illuminate\Console\Command;

class AdminCommand extends Command
{

    protected $signature = "laradmin:admin {email}";

    protected $description = "Set 'admin' role to user";

    public function __construct()
    {
        parent::__construct();

    }

    public function handle()
    {
        app()['cache']->forget('spatie.permission.cache');
        $model = app(config('laradmin.user.model'));
        $user = $model->where('email', $this->argument('email'))->firstOrFail();
        $user->assignRole('admin', config('laradmin.guard'));

        $this->line('Cool Cool Cool :)');
    }

}