<?php

namespace Shemi\Laradmin\Tests\Controller;

class AuthControllerTest extends AbstractControllerTest
{

    public function setUp()
    {
        parent::setUp();
    }

    /** @test */
    public function is_serve_login_page()
    {
        $this->get(route('laradmin.login'))
            ->assertSee('Login');
    }

    /** @test */
    public function it_return_form_errors()
    {
        $this->post(route('laradmin.login'), [
            'email' => 'test@test.com',
            'password' => 'password'
        ])
        ->assertJson([
            'form' => [trans('auth.failed')]
        ]);
    }

    /** @test */
    public function it_redirect_to_login_page()
    {
        $this->get(route('laradmin.dashboard'))
            ->assertRedirect(route('laradmin.login'));
    }

    /** @test */
    public function it_redirect_to_dashboard_after_success_sig_in()
    {
        $this->createUser([], 'admin');

        $this->post(route('laradmin.login'), [
            'email' => 'test@test.com',
            'password' => 'password'
        ])
        ->assertJsonFragment([
            'data' => [
                'redirect' => route('laradmin.dashboard')
            ]
        ]);

    }

}
