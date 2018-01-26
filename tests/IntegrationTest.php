<?php

namespace Shemi\Laradmin\Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Orchestra\Testbench\TestCase;
use Shemi\Laradmin\Data\DataManager;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Contracts\Role;
use Illuminate\Database\Schema\Blueprint;

abstract class IntegrationTest extends TestCase
{
    /**
     * Setup the test case.
     *
     * @return void
     * @throws \Exception
     */
    public function setUp()
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
        $this->withFactories(__DIR__.'/database/factories');
    }

    /**
     * Set up the database.
     *
     * @param \Illuminate\Foundation\Application $app
     * @throws \Exception
     */
    protected function setUpDatabase($app)
    {
        $this->loadLaravelMigrations(['--database' => 'testing']);

        $migrator = $app->make('migrator');
        if (!$migrator->repositoryExists()) {
            $this->artisan('migrate:install');
        }
        $migrator->run([realpath(__DIR__.'/database/migrations')]);
        $this->artisan('migrate', ['--path' => realpath(__DIR__.'/database/migrations')]);

        include_once __DIR__ . '/../vendor/spatie/laravel-permission/database/migrations/create_permission_tables.php.stub';
        (new \CreatePermissionTables())->up();

        include_once __DIR__ . '/../vendor/spatie/laravel-medialibrary/database/migrations/create_media_table.php.stub';
        (new \CreateMediaTable)->up();

        $this->createPermissions();

        $this->createRoleAndSetPermissions('admin', ['access backend']);
        $this->createRoleAndSetPermissions('admin_with_types_access', [
            'access backend',
            'browse types',
            'update types',
            'create types',
            'delete types'
        ]);
        $this->createRoleAndSetPermissions('admin_with_posts_access', [
            'access backend',
            'browse posts',
            'update posts',
            'create posts',
            'delete posts'
        ]);
    }

    protected function createPermissions()
    {
        $permissions = [
            'access backend',
            'browse menus',
            'update menus',
            'create menus',
            'delete menus',
            'browse types',
            'update types',
            'create types',
            'delete types',
            'browse posts',
            'update posts',
            'create posts',
            'delete posts'
        ];

        foreach ($permissions as $name) {
            $this->createPermission($name);
        }
    }

    protected function createPermission($name)
    {
        return $this->app[Permission::class]
            ->create(compact('name'));
    }

    protected function createRoleAndSetPermissions($role, $permissions)
    {
        return $this->app[Role::class]
            ->create(['name' => $role])
            ->givePermissionTo($permissions);
    }

    protected function restoreDataState()
    {
        $tree = [
            'defaults' => [
                'fa-icons.json',
                'md-icons.json'
            ],
            'menus' => [
                '1.json'
            ],
            'options' => [

            ],
            'types' => [
                '1.json',
                '2.json',
                '3.json',
                '4.json',
            ]
        ];

        $path = __DIR__.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'laradmin'.DIRECTORY_SEPARATOR;

        foreach ($tree as $folder => $files) {
            $allowFiles = array_merge($files, ['.gitkeep', '..', '.']);
            $manager = DataManager::location($folder);

            foreach (scandir($path.$folder) as $file) {
                if(! in_array($file, $allowFiles)) {
                    $manager->delete($file, null, true);
                }
            }
        }

    }

    /**
     * Tear down the test case.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->restoreDataState();

        parent::tearDown();
    }

    /**
     * Run the given assertion callback with a retry loop.
     *
     * @param  \Closure $callback
     * @return void
     * @throws \Exception
     */
    public function wait($callback)
    {
        retry(10, $callback, 1000);
    }

    /**
     * Get the service providers for the package.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return ['Shemi\Laradmin\LaradminServiceProvider'];
    }

    /**
     * Configure the environment.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.env', 'test');
        $app['config']->set('filesystems.disks.test', [
            'driver' => 'local',
            'root' => __DIR__.DIRECTORY_SEPARATOR.'data',
        ]);

        $app['config']->set('laradmin.storage.data_disk', 'test');

        $app['config']->set('app.key', 'base64:skzdEVpefCXrAbslLq8Wq3TMADpdEyUSBwu3zPlF4g8=');
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}