<?php

namespace Shemi\Laradmin\FormFields;

use Illuminate\Database\Eloquent\Model;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;
use Shemi\Laradmin\Traits\Renderable;

abstract class FormField implements FieldContract
{
    use Renderable;

    protected $name;
    protected $codename;

    /**
     * @param Field $field
     * @param Type $type
     * @param Model $model
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

}