<?php

namespace Shemi\Laradmin\Tests;

use Orchestra\Testbench\TestCase;
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

        include_once __DIR__.'/../vendor/spatie/laravel-permission/database/migrations/create_permission_tables.php.stub';

        (new \CreatePermissionTables())->up();

        $app[Role::class]
            ->create(['name' => 'admin'])
            ->givePermissionTo(
                $app[Permission::class]
                    ->create(['name' => 'access backend'])
            );

    }

    /**
     * Tear down the test case.
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Run the given assertion callback with a retry loop.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public function wait($callback)
    {
        retry(10, $callback, 1000);
    }

    /**
     * Get the service providers for the package.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return ['Shemi\Laradmin\LaradminServiceProvider'];
    }

    /**
     * Configure the environment.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.env', 'test');

        $app['config']->set('filesystems.disks.test', [
            'driver' => 'local',
            'root' => __DIR__ . '/data',
        ]);

        $app['config']->set('laradmin.storage.data_disk', 'test');

        $app['config']->set('app.key', 'base64:skzdEVpefCXrAbslLq8Wq3TMADpdEyUSBwu3zPlF4g8=');
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }
}