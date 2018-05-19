<?php

namespace Shemi\Laradmin\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Shemi\Laradmin\Models\User;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

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

        $role = app('laradmin')->manager('roles')->getDefaultRole();

        try {
            $user->assignRole($role);
        } catch (RoleDoesNotExist $e) {
            $this->alert("The role '{$role}' dose not exists, run the laradmin:roles commend.");

            return;
        }

        $this->info('Cool Cool Cool :)');
    }

}