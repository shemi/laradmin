<?php

namespace Shemi\Laradmin\FormFields\Traits;

use Shemi\Laradmin\JsonSchema\Blueprint;
use Shemi\Laradmin\JsonSchema\ObjectBlueprint;
use Shemi\Laradmin\JsonSchema\Schema;
use Shemi\Laradmin\Models\Type;

trait HasJsonSchema
{

    protected function registerBlueprintMacros()
    {
        $this->registerTemplateOptionsBlueprintMacro();
        $this->registerCommonFormFieldSchemaMacro();
        $this->registerVisibilityBlueprintMacro();
        $this->registerValidationBlueprintMacro();
        $this->registerBrowseSettingsBlueprintMacro();
        $this->registerOptionsBlueprintMacro();
        $this->registerRelationshipBlueprintMacro();
        $this->registerMediaBlueprintMacro();
    }

    public function registerCommonFormFieldSchemaMacro()
    {
        Blueprint::macro('commonFormFieldSchema', function() {
            $this->string('key')
                ->minLength(1)
                ->required();

            $this->string('label')
                ->minLength(1)
                ->required();

            $this->string('type')
                ->enum(app('laradmin')->getFormFieldNames());

            $this->string('id')
                ->minLength(5);

            $this->boolean('nullable')
                ->required()
                ->nullable();

            $this->boolean('read_only')
                ->required()
                ->nullable();

            $this->boolean('show_label')
                ->required()
                ->nullable();

            $this->anyOf('default_value', function(Blueprint $schema) {
                $schema->null();
                $schema->string();
                $schema->object();
                $schema->boolean();
                $schema->array();
            })->required();
        });
    }

    public function registerVisibilityBlueprintMacro()
    {
        $options = $this->getVisibilityOptions();

        Blueprint::macro('visibility', function() use ($options) {
            $this->array(
                'visibility',
                function(Blueprint $schema) use ($options) {
                    $schema->string()
                        ->enum($options);
                }
            )->uniqueItems()->required();
        });
    }

    protected function registerTemplateOptionsBlueprintMacro()
    {
        $subTypes = $this->getSubTypes();
        $sizes = $this->templateOptionsSizes;

        Blueprint::macro('templateOptions', function () use ($subTypes, $sizes) {
            return $this->object('template_options', function (Blueprint $schema) use ($subTypes, $sizes) {

                $schema->string('placeholder')
                    ->nullable();

                $schema->string('size')
                    ->enum($sizes)
                    ->nullable();

                $type = $schema->string('type');

                if($subTypes) {
                    $type->enum($subTypes);
                } else {
                    $type->nullable();
                }

                $schema->string('transform')
                    ->nullable();

                $schema->string('position')
                    ->enum(['is-left', 'is-center', 'is-right'])
                    ->nullable();

                $schema->string('show_if')
                    ->nullable();

                $schema->string('icon')
                    ->nullable();

                $schema->boolean('grouped');

                $schema->boolean('group_multiline');

                $schema->number('max_length')->nullable();

            })->required();
        });
    }

    protected function registerValidationBlueprintMacro()
    {
        Blueprint::macro('validation', function() {
            return $this->array('validation', function(Blueprint $schema) {
                $schema->string();
            })
            ->uniqueItems()
            ->required();
        });
    }

    protected function registerOptionsBlueprintMacro()
    {
        Blueprint::macro('options', function() {
            return $this->array('options', function(Blueprint $schema) {
                $schema->object(null, function (Blueprint $schema) {
                    $schema->oneOf('key', function(Blueprint $schema) {
                        $schema->string();
                        $schema->number();
                        $schema->null();
                    })->required();

                    $schema->oneOf('label', function(Blueprint $schema) {
                        $schema->string();
                        $schema->number();
                    })->required();
                });
            });
        });
    }

    public function registerBrowseSettingsBlueprintMacro()
    {
        Blueprint::macro('browseSettings', function() {
            return $this->object('browse_settings', function (Blueprint $schema) {

                $schema->number('order')
                    ->required()
                    ->nullable();

                $schema->string('label')
                    ->nullable();

                $schema->boolean('sortable');

                $schema->boolean('searchable');

                $schema->string('search_comparison')
                    ->enum(['=', '>=', '>', 'like', '<', '<=']);

                $schema->string('date_format')
                    ->nullable();

            })->required();
        });
    }

    public function registerRelationshipBlueprintMacro()
    {
        Blueprint::macro('relationship', function() {
            return $this->oneOf('relationship', function (Blueprint $schema) {

                $schema->null();

                $schema->object(null, function(Blueprint $schema) {

                    $schema->boolean('ajax_powered');

                    $schema->oneOf('label', ['array', 'string'])
                        ->required();

                    $schema->string('image')->nullable();

                    $schema->string('type')
                        ->enum(
                            Type::browseAll()
                                ->pluck('slug')
                                ->values()
                                ->unique()
                                ->values()
                                ->toArray()
                        )
                        ->nullable();
                });

            })->required();
        });
    }

    public function registerMediaBlueprintMacro()
    {
        Blueprint::macro('media', function() {

            return $this->object('media', function (Blueprint $schema) {

                $schema->string('disk')
                    ->enum(array_keys(config('filesystems.disks', [])))
                    ->required();

            })->required();

        });
    }

    protected function customSchema(Blueprint $schema, ObjectBlueprint $root)
    {

    }

    public function schema()
    {
        $this->registerBlueprintMacros();

        return Schema::create('input', function(Blueprint $schema, ObjectBlueprint $root) {
            $schema->commonFormFieldSchema();
            $schema->visibility();
            $schema->validation();
            $schema->templateOptions();
            $schema->browseSettings();
            $this->customSchema($schema, $root);
        });
    }


}