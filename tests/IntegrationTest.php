<?php

namespace Shemi\Laradmin\Tests;

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
     */
    public function setUp()
    {
        parent::setUp();

        $this->setUpDatabase($this->app);

    }

    /**
     * Set up the database.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        $this->loadLaravelMigrations(['--database' => 'testing']);

        $app['db']->connection()->getSchemaBuilder()->create('admins', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
        });

        include_once __DIR__ . '/../vendor/spatie/laravel-permission/database/migrations/create_permission_tables.php.stub';

        (new \CreatePermissionTables())->up();

        $this->createPermissions();

        $this->createRoleAndSetPermissions('admin', ['access backend']);
    }

    protected function createPermissions()
    {
        $permissions = [
            'access backend',
            'browse menus',
            'update menus',
            'create menus',
            'delete menus'
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