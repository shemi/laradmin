<?php

namespace Shemi\Laradmin\Models\Traits;

use \Illuminate\Database\Eloquent\Model as EloquentModel;
use Shemi\Laradmin\Data\Model;
use Shemi\Laradmin\FormFields\FormFormField;
use Shemi\Laradmin\Managers\FormFieldsManager;
use Shemi\Laradmin\Models\Type;

/**
 * Shemi\Laradmin\Models\Traits\FieldHasFormField
 *
 * @property array|null $template_options
 * @property string $key
 * @property string $type
 * @property boolean $is_relationship
 * @property string $setting_type
 */

trait InteractsWithFormField
{
    /**
     * @return FormFieldsManager
     */
    protected function formFieldsManager()
    {
        return app('laradmin')->formFields();
    }

    /**
     * @return FormFormField
     */
    public function formField()
    {
        return $this->formFieldsManager()
            ->get($this->type);
    }

    public function getValidationRoles()
    {
        return $this->formField()
            ? $this->formField()->getValidationRoles($this)
            : [];
    }

    public function transformRequest($value)
    {
        return $this->formField()->transformRequest($this, $value);
    }

    public function transformResponse($value)
    {
        if(! $this->formFieldsManager()->exists($this->type)) {
            return $value;
        }

        return $this->formField()->transformResponse($this, $value);
    }

    /**
     * @param Model $type
     * @param array $data
     * @return string
     * @throws \Throwable
     */
    public function render(Model $type, $data)
    {
        if($this->is_relationship && array_key_exists($this->key, $data)) {
            $this->options = $data[$this->key];
        }

        return $this->formField()->handle($this, $type, $data);
    }

    public function getSettingTypeAttribute()
    {
        return optional($this->formField())
            ->getSettingsValueType($this);
    }

}