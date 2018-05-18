<?php

namespace Shemi\Laradmin\Console\Commands;

use Illuminate\Console\Command;
use Shemi\Laradmin\Models\Type;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesCommand extends Command
{

    protected $signature = "laradmin:roles";

    protected $description = "Create Laradmin Roles And Permissions";

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->line('Creating Laradmin roles and permissions...');

        $manager = app('laradmin')->manager('roles');

        $this->line('Clearing cache...');
        $manager->clearCache();

        $this->line('Creating Laradmin default roles and permissions...');
        $manager->createDefaultPermissionsAndAssign();

        $this->line('Creating types permissions...');
        $manager->createPermissionsForAllTypes();

        $this->line('DONE.');
    }

}