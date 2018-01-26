<?php

namespace Shemi\Laradmin\Tests\Controller;

use Shemi\Laradmin\Models\Menu;

class MenusControllerTest extends AbstractControllerTest
{

    public function setUp()
    {
        parent::setUp();

        $this->createRoleAndSetPermissions('admin_with_menus_access', [
            'access backend',
            'browse menus',
            'update menus',
            'create menus',
            'delete menus'
        ]);

        $this->createRoleAndSetPermissions('admin_without_menus_access', [
            'access backend'
        ]);

    }

    /** @test */
    public function a_user_can_browse_menus_if_have_permission()
    {
        $this->actingAs($this->createUser([], 'admin_with_menus_access'));

        $this->get(route('laradmin.menus.index'))
            ->assertStatus(200)
            ->assertSee('Menus');
    }

    /** @test */
    public function a_user_cant_browse_menus_if_have_no_permission()
    {
        $this->actingAs($this->createUser([], 'admin_without_menus_access'));

        $this->get(route('laradmin.menus.index'))
            ->assertStatus(403);
    }

    /** @test */
    public function a_user_cannot_crate_menu_if_have_no_permission()
    {
        $this->actingAs($this->createUser([], 'admin_without_menus_access'));

        $this->get(route('laradmin.menus.create'))
            ->assertStatus(403);

        $this->post(route('laradmin.menus.store'), [
            'name' => 'new menu',
            'items' => []
        ])
        ->assertStatus(403);
    }

    /** @test */
    public function a_user_can_create_menu_if_have_permission()
    {
        $this->actingAs($this->createUser([], 'admin_with_menus_access'));

        $this->get(route('laradmin.menus.create'))
            ->assertStatus(200)
            ->assertSee('New Menu');

        $res = $this->post(route('laradmin.menus.store'), [
            'name' => 'Test Menu',
            'items' => [],
        ]);

        $menu = Menu::whereSlug('test-menu');

        $res->assertStatus(200)
        ->assertJsonFragment([
            'data' => [
                'menu' => $menu->toJson(),
                'redirect' => route('laradmin.menus.edit', [
                    'menu' => $menu->slug
                ])
            ]
        ]);

    }

    /** @test */
    public function it_validate_and_normalize_menu_item()
    {
        $this->actingAs($this->createUser([], 'admin_with_menus_access'));

        $this->postJson(route('laradmin.menus.item.validation'), [
            'title' => 'Test title',
            'type' => 'route',
            'url' => '',
            'route_name' => 'does.not.exists'
        ])
        ->assertStatus(422);

        $this->postJson(route('laradmin.menus.item.validation'), [
            'title' => 'Test title',
            'type' => 'url',
            'url' => '/test/'
        ])
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'id', 'title', 'type',
                'route_name', 'url', 'icon',
                'in_new_window', 'css_class',
                'items', 'is_active'
            ]
        ]);

    }

    protected function createMenu($name = 'test menu', $items = [])
    {
        return Menu::create([
            'name' => $name,
            'items' => $items,
            'slug' => str_slug($name)
        ]);
    }

    /** @test */
    public function a_user_cannot_edit_menu_if_have_no_permission()
    {
        $this->actingAs($this->createUser([], 'admin_without_menus_access'));

        $menu = $this->createMenu();

        $this->get(route('laradmin.menus.edit', ['menu' => $menu->slug]))
            ->assertStatus(403);

        $this->put(route('laradmin.menus.update', ['menu' => $menu->id]), [
            'name' => 'new name',
            'items' => []
        ])
        ->assertStatus(403);
    }

    /** @test */
    public function a_user_can_edit_menu_if_have_permission()
    {
        $this->actingAs($this->createUser([], 'admin_with_menus_access'));

        $menu = $this->createMenu();

        $this->get(route('laradmin.menus.edit', ['menu' => $menu->slug]))
            ->assertStatus(200);

        $this->put(route('laradmin.menus.update', ['menu' => $menu->id]), [
            'name' => 'new name',
            'items' => []
        ])
        ->assertStatus(200);

        $menu->refresh();

        $this->assertEquals('new name', $menu->name);
    }

    /** @test */
    public function a_user_cannot_delete_menu_if_have_no_permission()
    {
        $this->actingAs($this->createUser([], 'admin_without_menus_access'));

        $menu = $this->createMenu();

        $this->delete(route('laradmin.menus.destroy', ['menu' => $menu->id]))
            ->assertStatus(403);
    }

    /** @test */
    public function a_user_can_delete_menu_if_have_permission()
    {
        $this->actingAs($this->createUser([], 'admin_with_menus_access'));

        $menu = $this->createMenu();

        $this->delete(route('laradmin.menus.destroy', ['menu' => $menu->id]))
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'action' => true,
                    'redirect' => route('laradmin.menus.index')
                ]
            ]);

        $this->assertNull(Menu::find($menu->id));
    }

}
