<?php

namespace Shemi\Laradmin\Tests\Controller;

use Shemi\Laradmin\Tests\Controller\Fakes\TestUser;
use Shemi\Laradmin\Tests\IntegrationTest;

abstract class AbstractControllerTest extends IntegrationTest
{
    public function setUp()
    {
        parent::setUp();
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('auth.providers.test_users', [
            'driver' => 'eloquent',
            'model' => TestUser::class,
        ]);
        $app['config']->set('auth.guards.web.provider', 'test_users');
        $app['config']->set('laradmin.user.model', TestUser::class);
    }

    /**
     * create test user
     *
     * @param array $attributes
     * @param null|string $role
     * @return TestUser
     */
    protected function createUser($attributes = [], $role = null)
    {
        $attributes = array_replace_recursive([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password')
        ], $attributes);

        $user = TestUser::forceCreate($attributes);

        if($role) {
            $user->assignRole($role);
        }

        return $user;
    }

}