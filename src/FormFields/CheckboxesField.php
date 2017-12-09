<?php

namespace Shemi\Laradmin\FormFields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;

class CheckboxesField extends FormField
{

    protected $codename = "checkboxes";

    protected $builderSchema = [
        'template_options' => [
            'grouped' => true,
            'size' => null,
            'show_if' => null
        ],
    ];

    public function createContent(Field $field, Type $type, Model $model, $data)
    {
        return view('laradmin::formFields.checkboxes', compact(
            'field',
            'type',
            'model',
            'data'
        ));
    }

    public function transformRequest(Field $field, $data)
    {
        return (array) array_values($data);
    }

    protected function builderOptions(Collection $defaultOptions)
    {
        return $defaultOptions->merge([
            $this->getTemplateOptionsIsGroupedOption(),
            $this->getTemplateOptionsSizeOption(),
            $this->getTemplateOptionsPositionOption()
        ]);
    }

}