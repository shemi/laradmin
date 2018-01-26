<?php

$factory->define(\Shemi\Laradmin\Tests\Controller\Fakes\Category::class, function (Faker\Generator $faker) {
    return [
        'title' => $faker->sentence
    ];
});