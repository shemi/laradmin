<?php

namespace Shemi\Laradmin\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Shemi\Laradmin\Exceptions\InvalidArgumentException;
use Shemi\Laradmin\Models\User;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\Models\Role;

class AdminCommand extends Command
{

    protected $signature = "laradmin:admin {identifier} {attribute=email}";

    protected $description = "Set 'admin' role to user";

    public function __construct()
    {
        parent::__construct();

    }

    public function handle()
    {
        $this->line('');

        app()['cache']->forget('spatie.permission.cache');

        $model = app(config('laradmin.user.model'));

        if(! $model instanceof User) {
            $this->alert("The model \"" . get_class($model) . "\" most extend \"" . User::class . "\"");

            return;
        }

        $attribute = $this->argument('attribute');
        $identifier = $this->argument('identifier');

        /** @var User|Model $user */
        $user = $model->where($attribute, $identifier)->first();

        if(! $user) {
            $this->alert("A user where `{$attribute}` === '{$identifier}' cannot be found.");

            return;
        }

        try {
            $user->assignRole('admin');
        } catch (RoleDoesNotExist $e) {
            $this->alert("The role 'admin' dose not exists, run the laradmin:roles commend.");

            return;
        }

        $this->info('Cool Cool Cool :)');
    }

}