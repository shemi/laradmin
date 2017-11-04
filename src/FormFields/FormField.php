<?php

namespace Shemi\Laradmin\FormFields;

use Illuminate\Database\Eloquent\Model;
use Shemi\Laradmin\Contracts\FieldContract;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;
use Shemi\Laradmin\Traits\Renderable;

abstract class FormField implements FieldContract
{
    use Renderable;

    protected $name;
    protected $codename;

    protected $visibilityOptions = [
        "browse",
        "create",
        "edit",
        "view",
        "export",
        "import"
    ];

    protected $defaultBuilderSchema = [
        'id' => null,
        'label' => '',
        'key' => '',
        'show_label' => true,
        'read_only' => false,
        'default_value' => null,
        'validation' => [],
        'relationship' => null,
        'visibility' => ["browse", "create", "edit"],
        'template_options' => [
            'size' => null,
            'show_if' => null
        ],
        'browse_settings' => [
            'order' => null,
            'sortable' => false,
            'searchable' => false,
            'search_comparison' => 'like'
        ]
    ];

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

    public function getBuilderSchema()
    {
        $schema = $this->defaultBuilderSchema;

        $schema['type'] = $this->getCodename();

        if(property_exists($this, 'builderSchema')) {
            $schema = array_replace_recursive($schema, $this->builderSchema);
        }

        return $schema;
    }

    public function getBuilderOptions()
    {
//        $schema = $this->defaultBuilderSchema;
//
//        if(property_exists($this, 'builderSchema')) {
//            $schema = array_replace_recursive($schema, $this->builderSchema);
//        }

        return [];
    }

    public function getSubTypes()
    {
        return property_exists($this, 'subFields') ?
            $this->subFields :
            null;
    }

    public function getVisibilityOptions()
    {
        return $this->visibilityOptions;
    }

}