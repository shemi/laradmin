<?php

namespace Shemi\Laradmin\FormFields;

use Illuminate\Database\Eloquent\Model;
use Shemi\Laradmin\Contracts\FieldContract;
use Shemi\Laradmin\FormFields\Traits\Buildable;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;
use Shemi\Laradmin\Traits\Renderable;

abstract class FormField implements FieldContract
{
    use Renderable,
        Buildable;

    protected $name;

    protected $codename;

    public function __construct()
    {
        $this->registerBlueprintMacros();
    }

    /**
     * @param Field $field
     * @param Type $type
     * @param Model $model
     * @param $data
     * @return string
     */
    public function handle(Field $field, Type $type, Model $model, $data)
    {
        $content = $this->createContent($field, $type, $model, $data);

        return $this->render($content);
    }

    public function getCodename()
    {
        if (empty($this->codename)) {
            $name = class_basename($this);

            if (ends_with($name, 'Field')) {
                $name = substr($name, 0, -strlen('Field'));
            }

            $this->codename = snake_case($name);
        }

        return $this->codename;
    }

    public function getName()
    {
        if (empty($this->name)) {
            $this->name = ucwords(str_replace('_', ' ', $this->getCodename()));
        }

        return $this->name;
    }

    public function transformRequest(Field $field, $data)
    {
        if($field->nullable != false) {
            return $data === $field->nullable ? null : $data;
        }

        return $data;
    }

    public function transformResponse(Field $field, $data)
    {
        return $data;
    }

    public function getValidationRoles(Field $field)
    {
        if(! $field->validation || empty($field->validation)) {
            return false;
        }

        return ["{$field->key}" => $field->validation];
    }

}