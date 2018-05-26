<?php

namespace Shemi\Laradmin\FormPanels\Traits;

use Shemi\Laradmin\JsonSchema\Blueprint;
use Shemi\Laradmin\JsonSchema\ObjectBlueprint;
use Shemi\Laradmin\JsonSchema\Schema;
use Shemi\Laradmin\Models\Panel;

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

            $schema->string('title')->required();

            $schema->string('object_type')
                ->enum([Panel::OBJECT_TYPE]);

            $schema->string('type')->required();

            $schema->string('position')->required();

            $schema->boolean('is_main_meta');

            $schema->array('fields', ['object'])->required();

            $schema->boolean('has_container');

            $schema->object('style');

            $this->customSchema($schema, $root);
        });
    }


}