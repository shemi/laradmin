<?php

namespace Shemi\Laradmin\FormFields;

use Carbon\Carbon;
use Shemi\Laradmin\JsonSchema\Blueprint;
use Shemi\Laradmin\JsonSchema\ObjectBlueprint;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Data\Model;
use Shemi\Laradmin\Models\Setting;

class DatetimeField extends FormFormField
{

    protected $codename = "datetime";

    public function createContent(Field $field, Model $type, $data)
    {
        return view('laradmin::formFields.datetime', compact(
            'field',
            'type',
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

        if(! $value instanceof Carbon) {
            return null;
        }

        $value->timezone('UTC');

        return $value->toIso8601String();
    }

    public function structure()
    {
        return array_replace_recursive(parent::structure(), [
            'template_options' => [
                'icon' => 'calendar',
                'placeholder' => null,
                'size' => null,
                'datetime' => [
                    'time_24hr' => true,
                    'weekNumbers' => false,
                    'altFormat' => 'F j, Y H:i',
                    'ariaDateFormat' => 'F j, Y H:i',
                    'allowInput' => false,
                    'defaultHour' => 8,
                    'defaultMinute' => 0,
                    'enableSeconds' => false,
                    'hourIncrement' => 1,
                    'maxDate' => null,
                    'locale' => 'default',
                    'minDate' => null,
                    'timezone' => 'local',
                    'minuteIncrement' => 5,
                    'shorthandCurrentMonth' => false
                ]
            ]
        ]);
    }

    protected function customSchema(Blueprint $schema, ObjectBlueprint $root)
    {
        $schema->string('default_value')
            ->format('date')
            ->nullable()
            ->required();

        $schema->template_options->properties(function(Blueprint $schema) {
            $schema->object('datetime', function(Blueprint $schema) {
                $timezones = \DateTimeZone::listIdentifiers();
                $timezones[] = 'local';

                $schema->boolean('time_24hr')
                    ->title("Displays time picker in 24 hour mode without AM/PM selection when enabled.");

                $schema->boolean('weekNumbers')
                    ->title("Enables display of week numbers in calendar.");

                $schema->string('altFormat')
                    ->title("A string of characters which are used to define how the date will be displayed in the input box. The supported characters are defined in https://flatpickr.js.org/formatting/");

                $schema->string('ariaDateFormat')
                    ->title("Defines how the date will be formatted in the aria-label for calendar days, using the same tokens as dateFormat. If you change this, you should choose a value that will make sense if a screen reader reads it out loud.");

                $schema->boolean('allowInput')
                    ->title("Allows the user to enter a date directly input the input field. By default, direct entry is disabled.");

                $schema->number('defaultHour')
                    ->title("Initial value of the hour element.");

                $schema->number('defaultMinute')
                    ->title("Initial value of the minute element.");

                $schema->boolean('enableSeconds')
                    ->title("Enables seconds in the time picker.");

                $schema->number('hourIncrement')
                    ->title("Adjusts the step for the hour input (incl. scrolling)");

                $schema->string('maxDate')
                    ->nullable()
                    ->title("The maximum date that a user can pick to (inclusive).");

                $schema->string('minDate')
                    ->nullable()
                    ->title("The minimum date that a user can start picking from (inclusive).");

                $schema->string('timezone')
                    ->enum($timezones)
                    ->title("Defines in which time zone the date will be displayed in the input box.");

                $schema->number('minuteIncrement')
                    ->title("Adjusts the step for the minute input (incl. scrolling)");

                $schema->boolean('shorthandCurrentMonth')
                    ->title("Show the month using the shorthand version (ie, Sep instead of September).");
            })->required();
        });

    }

    public function getSettingsValueType(Field $field)
    {
        return Setting::TYPE_TIMESTAMP;
    }

}