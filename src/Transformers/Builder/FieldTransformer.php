<?php

namespace Shemi\Laradmin\Transformers\Builder;

use Shemi\Laradmin\Models\Field;

class FieldTransformer extends Transformer
{
    /**
     * @var static $inst
     */
    protected static $inst;

    protected $map = [
        'id' => 'string',
        'label' => 'string',
        'key' => 'string',
        'show_label' => 'bool',
        'default_value' => null,
        'nullable' => null,
        'value_manipulation' => 'string',
        'type' => 'string',
        'validation' => 'array',
        'visibility' => 'array',
        'options' => 'array|string',
        'template_options' => 'object',
        'read_only' => 'bool',
        'browse_settings' => 'object',
        'relationship' => 'object|bool',
        'media' => 'object',
        'tab_id' => null
    ];

    public static function transform(Field $field)
    {
        if(! static::$inst) {
            static::$inst = new static;
        }

        return static::$inst->handle($field);
    }

    public function handle(Field $field)
    {
        $return = [];

        foreach ($this->map as $key => $type) {
            $return[$key] = $this->cast($field->{$key}, $type);
        }

        if($field->fields->isNotEmpty()) {
            $return['fields'] = $field->fields->map(function($field) {
                return static::transform($field);
            });
        }

        $originalStructure = $field->formField()->structure();

        if(isset($return['template_options']) && isset($originalStructure['template_options'])) {
            $original = (array) $originalStructure['template_options'];
            $current = (array) $return['template_options'];

            $return['template_options'] = static::arrayMergeRecursiveDistinct($original, $current);
        }

        if(isset($return['browse_settings']) && isset($originalStructure['browse_settings'])) {
            $original = (array) $originalStructure['browse_settings'];
            $current = (array) $return['browse_settings'];

            $return['browse_settings'] = static::arrayMergeRecursiveDistinct($original, $current);
        }

        $return['object_type'] = Field::OBJECT_TYPE;

        return $return;
    }

}