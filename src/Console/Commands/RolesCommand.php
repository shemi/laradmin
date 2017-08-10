<?php

namespace Shemi\Laradmin\Console\Commands;

use Illuminate\Console\Command;
use Shemi\Laradmin\Models\Type;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesCommand extends Command
{

    protected $signature = "laradmin:roles";

    protected $description = "Create Roles And Permissions";

    protected $adminPermissions = [
        'access backend',
        'browse options',
        'update options',
        'browse menus',
        'update menus',
        'create menus'
    ];

    protected $permissions = [
        'import',
        'export',
        'create',
        'delete',
        'update',
        'browse',
        'view'
    ];

    public function __construct()
    {
        parent::__construct();



    }

    public function handle()
    {
        app()['cache']->forget('spatie.permission.cache');

        $this->line('Creating "admin" role');
        $adminRole = Role::firstOrNew(['name' => 'admin']);
        $line = 'The "admin" Role Exists.';

        if(! $adminRole->exists) {
            $adminRole->save();
            $line = '"admin" Role Created.';
        }

        $this->line($line);

        $this->line('Setting default admin permissions');

        foreach ($this->adminPermissions as $permission) {
            $this->createPermission($permission, $adminRole);
        }

        $types = Type::all();
        foreach ($types as $type) {
            $slug = str_plural($type->slug);

            foreach ($this->permissions as $permission) {
                $this->createPermission("{$permission} {$slug}", $adminRole);
            }
        }

    }

    protected function createPermission($name, Role $role)
    {
        $this->line('***********************************************');

        $this->line('Creating "'. $name .'" permission');
        $permission = Permission::firstOrNew(['name' => $name]);
        $line = 'The "'. $name .'" permission Exists.';

        if(! $permission->exists) {
            $permission->save();
            $line = '"'. $name .'" permission Created.';
        }

        $this->line($line);


        if(! $role->hasPermissionTo($permission)) {
            $this->line('Role "'. $role->name .'" give permission to "'. $name .'"');
            $role->givePermissionTo($permission);
        }

    }

}