<?php

namespace Shemi\Laradmin\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Migrations\Migrator;

class InstallCommand extends Command
{

    protected $signature = "laradmin:install";

    protected $description = "Install Laradmin";

    public function __construct()
    {
        parent::__construct();



    }

    public function handle()
    {
        $migrationFiles = glob(database_path('/migrations/*.php'));
        $migrationExists = false;

        foreach ($migrationFiles as $file) {
            if(preg_match("/(\d{4}_\d{2}_\d{2}_\d{6})(_create_permission_tables\.php$)/", basename($file))) {
                $migrationExists = true;

                break;
            }
        }

        if(! $migrationExists) {
            $this->line('Publishing laravel-permission migrations');

            $this->call("vendor:publish", [
                '--provider' => \Spatie\Permission\PermissionServiceProvider::class,
                '--tag' => 'migrations'
            ]);
        }

        $this->line('Publishing laravel-permission config file');

        $this->call("vendor:publish", [
            '--provider' => \Spatie\Permission\PermissionServiceProvider::class,
            '--tag' => 'config'
        ]);

    }

}