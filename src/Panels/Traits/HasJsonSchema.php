<?php

namespace Shemi\Laradmin\Panels\Traits;

use Shemi\Laradmin\JsonSchema\Blueprint;
use Shemi\Laradmin\JsonSchema\ObjectBlueprint;
use Shemi\Laradmin\JsonSchema\Schema;

trait HasJsonSchema
{

    protected function customSchema(Blueprint $schema, ObjectBlueprint $root)
    {

    }

    public function schema()
    {
        return Schema::create('panel', function(Blueprint $schema, ObjectBlueprint $root) {

            $schema->oneOf('id', function(Blueprint $schema) {
                $schema->integer();
                $schema->string();
            });

            $schema->string('title');

            $schema->string('type');

            $schema->integer('position')
                ->nullable();

            $schema->boolean('is_main_meta');

            $schema->array('fields', ['object']);

            $schema->boolean('has_container');

            $schema->object('style');

            $this->customSchema($schema, $root);
        });
    }


}