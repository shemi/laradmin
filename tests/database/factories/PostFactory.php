<?php

$factory->define(\Shemi\Laradmin\Tests\Controller\Fakes\Post::class, function (Faker\Generator $faker) {
    return [
        'title' => $title = $faker->sentence,
        'slug' => str_slug($title),
        'user_id' => factory(\Shemi\Laradmin\Models\User::class)->create()->id,
    ];
});