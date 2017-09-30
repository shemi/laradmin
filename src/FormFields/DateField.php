<?php

namespace Shemi\Laradmin\FormFields;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;

class DateField extends FormField
{

    protected $codename = "date";

    public function createContent(Field $field, Type $type, Model $model, $data)
    {
        return view('laradmin::formFields.date', compact(
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
            $value = Carbon::parse($value, 'utc');
        }

        return $value;
    }

    public function transformResponse(Field $field, $data)
    {
        $value = parent::transformResponse($field, $data);

        if(is_array($value) && array_key_exists('date', $value)) {
            $value = Carbon::parse($value['date'], 'utc');
        }

        if(is_string($value)) {
            $value = Carbon::parse($value);
        }

        return $value instanceof Carbon ? $value->toIso8601String() : null;
    }

}