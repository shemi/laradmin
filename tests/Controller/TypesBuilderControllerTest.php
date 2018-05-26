<?php

namespace Shemi\Laradmin\Tests\Controller;

use Shemi\Laradmin\Http\Controllers\CrudController;
use Shemi\Laradmin\Http\Controllers\ExportController;
use Shemi\Laradmin\Http\Controllers\ImportController;
use Shemi\Laradmin\Models\Panel;
use Shemi\Laradmin\Models\Type;
use Shemi\Laradmin\Models\User;
use Shemi\Laradmin\Tests\Controller\Fakes\TestCrudController;
use Shemi\Laradmin\Tests\Controller\Fakes\TestUser;

class TypesBuilderControllerTest extends AbstractControllerTest
{

    /** @test */
    public function a_user_can_browse_types_if_have_permission()
    {
        $this->actingAs($this->createUser([], 'admin_with_types_access'));

        $this->get(route('laradmin.types.index'))
            ->assertStatus(200)
            ->assertSee('Types Builder');

        $this->get(route('laradmin.types.query'))
            ->assertStatus(200);

    }

    /** @test */
    public function a_user_cant_browse_types_if_have_no_permission()
    {
        $this->actingAs($this->createUser([], 'admin'));

        $this->get(route('laradmin.types.index'))
            ->assertStatus(403);

        $this->get(route('laradmin.types.query'))
            ->assertStatus(403);
    }

    protected function getValidTypeArray($name = 'new type',
                                         $controller = null,
                                         $model = null,
                                         $panels = null,
                                         $export_controller = null,
                                         $import_controller = null)
    {
        return [
            'name' => $name,
            'model' => $model ?: TestUser::class,
            'controller' => $controller ?: CrudController::class,
            'support_export' => $export_controller ? true : false,
            'export_controller' => $export_controller,
            'support_import' => $import_controller ? true : false,
            'import_controller' => $import_controller,
            'default_sort' => 'id',
            'default_sort_direction' => 'DESC',
            'icon' => null,
            'records_per_page' => 25,
            'panels' => $panels ?: [
                [
                    'id' => '123456',
                    'has_container' => true,
                    'is_main_meta' => false,
                    'position' => 'main',
                    'style' => (object) [],
                    'title' => 'cool',
                    'object_type' => Panel::OBJECT_TYPE,
                    'fields' => (array) []
                ]
            ]
        ];
    }

    /** @test */
    public function a_user_cannot_crate_type_if_have_no_permission()
    {
        $this->actingAs($this->createUser([], 'admin'));

        $this->get(route('laradmin.types.create'))
            ->assertStatus(403);

        $this->post(route('laradmin.types.store'), $this->getValidTypeArray())
            ->assertStatus(403);
    }

    /** @test */
    public function is_making_sure_that_the_controller_class_exists_and_valid()
    {
        $this->actingAs($this->createUser([], 'admin_with_types_access'));

        $this->postJson(
            route('laradmin.types.store'),
            $this->getValidTypeArray(
                'new type',
                '\App\Http\Controllers\DoesNotExistsController',
                null
            )
        )
        ->assertStatus(422)
        ->assertJsonValidationErrors([
            'controller'
        ]);

        $this->postJson(
            route('laradmin.types.store'),
            $this->getValidTypeArray(
                'new type',
                TestUser::class,
                null
            )
        )
        ->assertStatus(422)
        ->assertJsonValidationErrors([
            'controller'
        ]);

    }

    /** @test */
    public function is_making_sure_that_the_model_class_exists_and_valid()
    {
        $this->actingAs($this->createUser([], 'admin_with_types_access'));

        $this->postJson(
            route('laradmin.types.store'),
            $this->getValidTypeArray(
                'new type',
                null,
                '\App\DoesNotExistsModel'
            )
        )
        ->assertStatus(422)
        ->assertJsonValidationErrors([
            'model'
        ]);
    }

    /** @test */
    public function is_making_sure_that_the_import_controller_class_exists_and_valid()
    {
        $this->actingAs($this->createUser([], 'admin_with_types_access'));

        $this->postJson(
            route('laradmin.types.store'),
            $this->getValidTypeArray(
                'new type',
                null,
                null,
                null,
                null,
                '\App\DoesNotExistsController'
            )
        )
        ->assertStatus(422)
        ->assertJsonValidationErrors([
            'import_controller'
        ]);
    }

    /** @test */
    public function is_making_sure_that_the_export_controller_class_exists_and_valid()
    {
        $this->actingAs($this->createUser([], 'admin_with_types_access'));

        $this->postJson(
            route('laradmin.types.store'),
            $this->getValidTypeArray(
                'new type',
                null,
                null,
                null,
                '\App\DoesNotExistsController'
            )
        )
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'export_controller'
            ]);
    }

    /** @test */
    public function a_user_can_crate_type_if_have_permission()
    {
        $this->actingAs($this->createUser([], 'admin_with_types_access'));

        $this->get(route('laradmin.types.create'))
            ->assertStatus(200)
            ->assertSee('New Type');

        $this->postJson(
            route('laradmin.types.store'),
            $this->getValidTypeArray('Test')
        )
        ->assertStatus(200);

        $this->assertInstanceOf(Type::class, Type::whereSlug('test'));
    }

    /** @test */
    public function a_user_cannot_update_type_if_have_no_permission()
    {
        $this->actingAs($this->createUser([], 'admin'));

        $type = Type::whereSlug('users');

        $this->get(route('laradmin.types.edit', ['type' => $type->slug]))
            ->assertStatus(403);

        $this->put(
            route('laradmin.types.update', ['type' => $type->slug]),
            $this->getValidTypeArray()
        )
        ->assertStatus(403);
    }

    /** @test */
    public function a_user_can_update_type_if_have_permission()
    {
        $this->actingAs($this->createUser([], 'admin_with_types_access'));

        /** @var Type $type */
        $type = Type::create([
            'name' => 'test',
            'slug' => 'test',
            'controller' => CrudController::class,
            'model' => TestUser::class,
            'panels' => (array) [],
            'records_per_page' => 25,
            'support_export' => false,
            'export_controller' => ExportController::class,
            'support_import' => false,
            'default_sort' => 'id',
            'import_controller' => ImportController::class,
            'icon' => null,
        ]);

        $this->get(route('laradmin.types.edit', ['type' => $type->slug]))
            ->assertStatus(200);

        $this->putJson(
            route('laradmin.types.update', ['type' => $type->slug]),
            $this->getValidTypeArray('New Test', TestCrudController::class, User::class, (array) [
                [
                    'id' => '123456',
                    'has_container' => true,
                    'is_main_meta' => false,
                    'position' => 'main',
                    'style' => (object) [],
                    'title' => 'cool',
                    'fields' => (array) []
                ]
            ])
        )
        ->assertStatus(200);

        $type->refresh();

        $this->assertEquals('New Test', $type->name);
        $this->assertEquals(str_slug('New Test'), $type->slug);
        $this->assertEquals(TestCrudController::class, $type->controller);
        $this->assertEquals(User::class, $type->model);
        $this->assertCount(1, $type->panels);
    }

}