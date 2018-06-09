<?php

namespace Shemi\Laradmin\FormFields;

use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Data\Model;
use Shemi\Laradmin\Models\Setting;

class SwitchField extends FormFormField
{

    protected $codename = "switch";

    public function createContent(Field $field, Model $type, $data)
    {
        return view('laradmin::formFields.switch', compact(
            'field',
            'type',
            'data'
        ));
    }

    public function transformRequest(Field $field, $data)
    {
        if($field->nullable != false) {
            return $data == $field->nullable ? null : $data;
        }

        return (bool) $data;
    }

    public function transformResponse(Field $field, $data)
    {
        return (bool) $data;
    }

    public function structure()
    {
        $structure = parent::structure();

        return array_replace_recursive($structure, [
            'nullable' => false,
            'default_value' => false,
            'template_options' => [
                'size' => null,
                'show_if' => null
            ]
        ]);
    }

    public function getSettingsValueType(Field $field)
    {
        return Setting::TYPE_BOOLEAN;
    }

}