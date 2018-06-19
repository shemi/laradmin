<?php

namespace Shemi\Laradmin\FormFields;

use Carbon\Carbon;
use Shemi\Laradmin\JsonSchema\Blueprint;
use Shemi\Laradmin\JsonSchema\ObjectBlueprint;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Data\Model;
use Shemi\Laradmin\Models\Setting;

class TimeField extends DatetimeField
{

    protected $codename = "time";

    public function structure()
    {
        return array_replace_recursive(parent::structure(), [
            'template_options' => [
                'icon' => 'clock-o',
                'datetime' => [
                    'altFormat' => 'H:i',
                    'ariaDateFormat' => 'H:i'
                ]
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


    public function getSettingsValueType(Field $field)
    {
        return Setting::TYPE_TIME;
    }

}