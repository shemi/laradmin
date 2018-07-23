<?php

namespace Shemi\Laradmin\FormFields\Traits;

use Shemi\Laradmin\JsonSchema\Blueprint;
use Shemi\Laradmin\JsonSchema\ObjectBlueprint;
use Shemi\Laradmin\JsonSchema\Schema;
use Shemi\Laradmin\Managers\DynamicsManager;
use Shemi\Laradmin\Models\Field;
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
                ->title("Model key (e.g \$model->id)")
                ->required();

            $this->string('label')
                ->minLength(1)
                ->title("Field label (support dynamics e.g *config:app.name)")
                ->required();

            $this->string('type')
                ->title("Field type")
                ->enum(
                    app('laradmin')
                        ->manager('formFields')
                        ->allNames()
                );

            $this->string('object_type')
                ->enum([Field::OBJECT_TYPE]);

            $this->string('id')
                ->minLength(5);

            $this->boolean('nullable')
                ->title("If the field value equal to this value the model value will be null")
                ->required()
                ->nullable();

            $this->string('value_manipulation')
                ->title("Manipulate the user value before saving")
                ->required()
                ->nullable();

            $this->boolean('read_only')
                ->title("Is this field is read only field (e.g disabled)")
                ->required()
                ->nullable();

            $this->boolean('show_label')
                ->title("Is this field label is visible")
                ->required()
                ->nullable();

            $this->anyOf('default_value', ['null', 'string', 'object', 'number', 'boolean', 'array'])
                ->title("The field default value")
                ->required();
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
            )
            ->uniqueItems()
            ->title("In which views this field will be shown")
            ->required();
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
                    ->title("Vertical size of input, optional")
                    ->enum($sizes)
                    ->nullable();

                $type = $schema->string('type');

                if($subTypes) {
                    $type->title("The field sub type")
                        ->enum($subTypes);
                } else {
                    $type->nullable();
                }

                $schema->string('position')
                    ->title("Which position should the addons appear, optional")
                    ->enum(['is-left', 'is-center', 'is-right'])
                    ->nullable();

                $schema->string('show_if')
                    ->title("v-if use the \"form\" name space to access the form object (e.g let's say you don't want to show this field if the \"first_name\" field is empty: \"! form.first_name\")")
                    ->nullable();

                $schema->string('icon')
                    ->title("Icon name to be added")
                    ->nullable();

                $schema->boolean('grouped')
                    ->title("Direct child components/elements of Field will be grouped horizontally");

                $schema->boolean('group_multiline')
                    ->title("Allow controls to fill up multiple lines, making it responsive");

                $schema->boolean('horizontal')
                    ->title("Group label and control on the same line for horizontal forms");

                $schema->number('max_length')
                    ->title("Same as native maxlength, plus character counter")
                    ->nullable();

            })
            ->title("The field display settings")
            ->required();
        });
    }

    protected function registerValidationBlueprintMacro()
    {
        Blueprint::macro('validation', function() {
            return $this->array('validation', function(Blueprint $schema) {
                $schema->string();
            })
            ->title("Laravel validation")
            ->uniqueItems()
            ->required();
        });
    }

    protected function registerOptionsBlueprintMacro()
    {
        Blueprint::macro('options', function() {

            return $this->anyOf('options', function(Blueprint $schema) {
                $schema->string(null)
                    ->pattern(DynamicsManager::REGEX);

                $schema->array(null, function(Blueprint $schema) {
                    $schema->object(null, function (Blueprint $schema) {
                        $schema->oneOf('key', function(Blueprint $schema) {
                            $schema->string();
                            $schema->number();
                        })->required();

                        $schema->oneOf('label', function(Blueprint $schema) {
                            $schema->string();
                            $schema->number();
                        })->required();
                    });
                });
            })->title("The select options if its not a relationship field (support dynamics)");

        });
    }

    public function registerBrowseSettingsBlueprintMacro()
    {
        Blueprint::macro('browseSettings', function() {
            return $this->object('browse_settings', function (Blueprint $schema) {

                $schema->number('order')
                    ->title("The column display order")
                    ->required()
                    ->nullable();

                $schema->string('label')
                    ->title("The column label defaults to the main label (support dynamics e.g *config:app.name)")
                    ->nullable();

                $schema->boolean('sortable')
                    ->title("Is this column support sort");

                $schema->boolean('searchable')
                    ->title("Determines if this column includes in the search query");

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

                $schema->boolean();

                $schema->object(null, function(Blueprint $schema) {

                    $schema->boolean('ajax_powered')
                        ->title("determines if the field options will load asynchronously");

                    $schema->string('order_key');

                    $schema->oneOf('label', ['array', 'string'])
                        ->title("The option label")
                        ->required();

                    $schema->string('key')
                        ->title("The option key")
                        ->required();

                    $schema->string('image')
                        ->title("The image collection name")
                        ->nullable();

                    $schema->string('type')
                        ->enum(
                            Type::browseAll()
                                ->pluck('slug')
                                ->values()
                                ->unique()
                                ->values()
                                ->toArray()
                        )
                        ->title("The related \"type\"")
                        ->nullable();

                    $schema->string('model')
                        ->title("The related model")
                        ->nullable();
                });

            })
            ->title("Determines if this field is relationship field")
            ->required();
        });
    }

    public function registerMediaBlueprintMacro()
    {
        Blueprint::macro('media', function() {

            return $this->object('media', function (Blueprint $schema) {

                $schema->string('disk')
                    ->title("To which disk save the media")
                    ->enum(array_keys(config('filesystems.disks', [])))
                    ->required();

            })
            ->title("The media settings")
            ->required();

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