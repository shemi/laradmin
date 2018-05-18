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
        $this->publishMigrations();

        if (! $this->option('dev')) {
            $this->publishAssets();
        } else {
            $this->symlinkAssetsFolder();
        }

        $this->publishConfig();

        $this->publishData();
    }

    protected function publishMigrations()
    {
        $migrationFiles = glob(database_path('/migrations/*.php'));
        $migrationExists = false;

        foreach ($migrationFiles as $file) {
            if (preg_match("/(\d{4}_\d{2}_\d{2}_\d{6})(_create_permission_tables\.php$)/", basename($file))) {
                $migrationExists = true;

                break;
            }
        }

        if (! $migrationExists) {
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

    protected function publishAssets()
    {
        $this->line('Publishing laradmin assets files');

        $this->call("vendor:publish", [
            '--provider' => LaradminServiceProvider::class,
            '--tag' => 'laradmin_assets'
        ]);
    }

    protected function symlinkAssetsFolder()
    {
        $this->line('Creating symlink to laradmin assets files');

        $ds = DIRECTORY_SEPARATOR;
        $laradminVendorPath = public_path($ds.'vendor'.$ds.'laradmin');

        if (! file_exists($laradminVendorPath)) {
            mkdir($laradminVendorPath, 0777, true);
        }

        $this->laravel->make('files')->link(
            __DIR__ . '/../../../publishable/public', public_path('/vendor/laradmin/assets')
        );
    }

    public function publishConfig()
    {
        $this->line('Publishing laradmin config file');

        $this->call("vendor:publish", [
            '--provider' => LaradminServiceProvider::class,
            '--tag' => 'laradmin_config'
        ]);
    }

    public function publishData()
    {
        $this->line('Publishing laradmin data files');

        $this->call("vendor:publish", [
            '--provider' => LaradminServiceProvider::class,
            '--tag' => 'laradmin_data'
        ]);
    }

}