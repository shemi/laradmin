<?php

namespace Shemi\Laradmin\FormFields;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Shemi\Laradmin\JsonSchema\Blueprint;
use Shemi\Laradmin\JsonSchema\ObjectBlueprint;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;

class TimeField extends FormFormField
{

    protected $codename = "time";

    public function createContent(Field $field, Type $type, Model $model, $data)
    {
        return view('laradmin::formFields.time', compact(
            'field',
            'type',
            'model',
            'data'
        ));
    }

    public function transformRequest(Field $field, $data)
    {
        $value = parent::transformRequest($field, $data);

        if($value) {
            $value = Carbon::parse($value, 'UTC');
        }

        return $value;
    }

    public function transformResponse(Field $field, $data)
    {
        $value = parent::transformResponse($field, $data);

        if(is_array($value) && array_key_exists('date', $value)) {
            $value = Carbon::parse($value['date'], 'UTC');
        }

        if(is_string($value)) {
            $value = Carbon::parse($value);
        }

        return $value instanceof Carbon ? $value->toIso8601String() : null;
    }

    public function structure()
    {
        return array_replace_recursive(parent::structure(), [
            'template_options' => [
                'icon' => null,
                'placeholder' => null,
                'size' => null,
                'format' => '24'
            ]
        ]);
    }

    protected function customSchema(Blueprint $schema, ObjectBlueprint $root)
    {
        $schema->string('default_value')
            ->format('time')
            ->nullable()
            ->required();
    }

}