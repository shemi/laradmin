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
        'type' => 'string',
        'validation' => 'array',
        'visibility' => 'array',
        'options' => 'array',
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

        $return['object_type'] = Field::OBJECT_TYPE;

        return $return;
    }

}