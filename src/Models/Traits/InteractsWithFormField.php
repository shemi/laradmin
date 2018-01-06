<?php

namespace Shemi\Laradmin\Models\Traits;

use \Illuminate\Database\Eloquent\Model as EloquentModel;
use Shemi\Laradmin\FormFields\FormFormField;
use Shemi\Laradmin\Models\Type;

/**
 * Shemi\Laradmin\Models\Traits\FieldHasFormField
 *
 * @property array|null $template_options
 * @property string $key
 * @property string $type
 * @property boolean $is_relationship
 */

trait InteractsWithFormField
{

    /**
     * @return FormFormField
     */
    public function formField()
    {
        return app('laradmin')->formField($this->type);
    }

    public function getValidationRoles()
    {
        $formField = $this->formField();

        return $formField
            ? $formField->getValidationRoles($this)
            : [];
    }

    public function transformRequest($value)
    {
        return $this->formField()->transformRequest($this, $value);
    }

    public function transformResponse($value)
    {
        if(! app('laradmin')->formFieldExists($this->type)) {
            return $value;
        }

        return $this->formField()->transformResponse($this, $value);
    }

    /**
     * @param Type $type
     * @param EloquentModel $model
     * @param array $data
     * @return string
     */
    public function render(Type $type, EloquentModel $model, $data)
    {
        if($this->is_relationship && array_key_exists($this->key, $data)) {
            $this->options = $data[$this->key];
        }

        return $this->formField()->handle($this, $type, $model, $data);
    }

}