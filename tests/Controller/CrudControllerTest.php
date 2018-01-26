<?php

namespace Shemi\Laradmin\Tests\Controller;


use Shemi\Laradmin\Tests\Controller\Fakes\Category;
use Shemi\Laradmin\Tests\Controller\Fakes\Post;

class CrudControllerTest extends AbstractControllerTest
{

    /** @test */
    public function a_user_cannot_browse_model_if_have_no_permission()
    {
        $this->actingAs($this->createUser([], 'admin'));

        $this->get(route('laradmin.posts.index'))
            ->assertStatus(403);

        $this->getJson(route('laradmin.posts.query'))
            ->assertStatus(403);
    }

    /** @test */
    public function a_user_can_browse_model_if_have_permission()
    {
        $this->actingAs($this->createUser([], 'admin_with_posts_access'));

        $this->get(route('laradmin.posts.index'))
            ->assertStatus(200);

        $this->getJson(route('laradmin.posts.query'))
            ->assertStatus(200);
    }

    /** @test */
    public function it_returns_list_of_posts()
    {
        $this->actingAs($this->createUser([], 'admin_with_posts_access'));

        factory(Post::class, 5)->create();

        $response = $this->getJson(route('laradmin.posts.query'))
            ->assertStatus(200);

        $this->assertEquals(5, $response->json()['data']['total']);

        factory(Post::class)->create(['title' => 'new post']);

        $response = $this->getJson(route('laradmin.posts.query', ['search' => 'new post']))
            ->assertStatus(200);

        $this->assertEquals(1, $response->json()['data']['total']);
    }

    /** @test */
    public function a_user_cannot_create_post_if_have_no_permission()
    {
        $this->actingAs($user = $this->createUser([], 'admin'));

        $this->get(route('laradmin.posts.create'))
            ->assertStatus(403);

        $this->postJson(route('laradmin.posts.store'), [
            'title' => 'new post',
            'slug' => 'new-post',
            'user' => $user->id,
            'categories' => []
        ])
        ->assertStatus(403);

    }

    /** @test */
    public function it_validates_the_request_by_the_fields_validation_object()
    {
        $this->actingAs($user = $this->createUser([], 'admin_with_posts_access'));

        $this->postJson(route('laradmin.posts.store'), [
            'title' => null,
            'slug' => null,
            'user' => null,
            'categories' => []
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['title', 'user']);
    }

    /** @test */
    public function a_user_can_create_post_if_have_permission()
    {
        $this->actingAs($user = $this->createUser([], 'admin_with_posts_access'));

        $this->get(route('laradmin.posts.create'))
            ->assertStatus(200)
            ->assertSee('New Post');

        $this->postJson(route('laradmin.posts.store'), [
            'title' => 'new post',
            'slug' => 'new-post-test',
            'user' => $user->id,
            'categories' => []
        ])
        ->assertStatus(200);

        $this->assertInstanceOf(Post::class, Post::where('slug', 'new-post-test')->first());
    }

    /** @test */
    public function a_user_cannot_update_post_if_have_no_permission()
    {
        $this->actingAs($user = $this->createUser([], 'admin'));

        $post = factory(Post::class)->create(['title' => 'new post']);

        $this->get(route('laradmin.posts.edit', ['post' => $post->id]))
            ->assertStatus(403);

        $this->putJson(route('laradmin.posts.update', ['post' => $post->id]), [
            'title' => 'new post title',
            'slug' => $post->slug,
            'user' => $post->user->id,
            'categories' => []
        ])
        ->assertStatus(403);

    }

    /** @test */
    public function a_user_can_update_post_if_have_permission()
    {
        $this->actingAs($user = $this->createUser([], 'admin_with_posts_access'));

        $post = factory(Post::class)->create(['title' => 'new post']);

        $categories = factory(Category::class)->times(2)->create();

        $this->get(route('laradmin.posts.edit', ['post' => $post->id]))
            ->assertStatus(200)
            ->assertSee('Edit Post');

        $this->putJson(route('laradmin.posts.update', ['post' => $post->id]), [
            'title' => 'new post title',
            'slug' => 'cool-slug',
            'user' => $user->id,
            'categories' => collect($categories)->transform(function($category) {
                return [
                    'key' => $category->id,
                    'label' => $category->name
                ];
            })->toArray()
        ])
        ->assertStatus(200);

        $post->refresh();

        $this->assertEquals('new post title', $post->title);
        $this->assertEquals('cool-slug', $post->slug);
        $this->assertEquals($user->id, $post->user->id);

        foreach ($categories as $category) {
            $this->assertCount(1, $post->categories->where('id', $category->id));
        }

    }

}