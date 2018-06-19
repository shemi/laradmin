<?php

namespace Shemi\Laradmin\FormFields;

use Carbon\Carbon;
use Shemi\Laradmin\JsonSchema\Blueprint;
use Shemi\Laradmin\JsonSchema\ObjectBlueprint;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Data\Model;
use Shemi\Laradmin\Models\Setting;

class DateField extends DatetimeField
{

    protected $codename = "date";

    public function structure()
    {
        return array_replace_recursive(parent::structure(), [
            'template_options' => [
                'datetime' => [
                    'altFormat' => 'F j, Y',
                    'ariaDateFormat' => 'F j, Y'
                ]
            ]
        ]);
    }

    protected function customSchema(Blueprint $schema, ObjectBlueprint $root)
    {
        parent::customSchema($schema, $root);

        $schema->string('default_value')
            ->format('date')
            ->nullable()
            ->required();
    }

    public function getSettingsValueType(Field $field)
    {
        return Setting::TYPE_DATE;
    }

}