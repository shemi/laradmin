<?php

namespace Shemi\Laradmin\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Migrations\Migrator;
use Shemi\Laradmin\LaradminServiceProvider;

class InstallCommand extends Command
{

    protected $signature = "laradmin:install {--dev}";

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

        if (! $this->option('dev')) {
            $this->line('Publishing laradmin assets files');

            $this->call("vendor:publish", [
                '--provider' => LaradminServiceProvider::class,
                '--tag' => 'laradmin_assets'
            ]);
        } else {
            $this->line('Creating symlink to laradmin assets files');

            if(! file_exists(public_path('/vendor/laradmin'))) {
                mkdir(public_path('/vendor/laradmin'), 0777, true);
            }

            symlink(
                __DIR__ . '/../../../publishable/public',
                public_path('/vendor/laradmin/assets')
            );
        }

        $this->line('Publishing laradmin config file');

        $this->call("vendor:publish", [
            '--provider' => LaradminServiceProvider::class,
            '--tag' => 'laradmin_config'
        ]);

        $this->line('Publishing laradmin data files');

        $this->call("vendor:publish", [
            '--provider' => LaradminServiceProvider::class,
            '--tag' => 'laradmin_data'
        ]);

    }

}