<?php

namespace Shemi\Laradmin\Tests\Controller;

class IconsControllerTest extends AbstractControllerTest
{

    /** @test */
    public function it_returns_array_of_icons()
    {
        $this->actingAs($this->createUser([], 'admin'));

        $this->get(route('laradmin.icons'))
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'icons' => [
                        [
                            'name',
                            'icons' => [
                                [
                                    'class',
                                    'isAlias',
                                    'name',
                                    'title'
                                ]
                            ]
                        ]
                    ]
                ]
            ]);
    }

}
