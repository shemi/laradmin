<?php

namespace Shemi\Laradmin\Tests\Controller;

class DashboardControllerTest extends AbstractControllerTest
{

    /** @test */
    public function it_serve_dashboard_to_authorized_users()
    {
        $this->actingAs($this->createUser([], 'admin'));

        $this->get(route('laradmin.dashboard'))
            ->assertSee('Dashboard');
    }

    /** @test */
    public function it_redirect_unauthorized_users()
    {
        $this->actingAs($this->createUser([]));

        $this->get(route('laradmin.dashboard'))
            ->assertRedirect();
    }

}
