<?php

namespace Shemi\Laradmin\Transformers\Request;

use Shemi\Laradmin\Data\Collection;
use Shemi\Laradmin\Models\Field;

class ValueTransformer
{
    /**
     * @var Collection $fields
     */
    protected $fields;

    public function __construct(Collection $fields)
    {

    }

    public function transform($value, Field $field)
    {
        $transform = explode(':', $field->getTemplateOption('transform', 'value'));

        if(! $value && count($transform) > 1) {
            $copyKey = $transform[1];

            $copyField = $this->fields
                ->where('key', $copyKey)
                ->first();

            if($copyField) {
                $value = $this->getFieldValue($copyField);
            }

            elseif ($this->model->offsetExists($copyKey)) {
                $value = $this->model->getAttribute($copyKey);
            }

            else {
                throw CreateUpdateTransformCantFindCopyFieldOrAttributeException::create($copyKey);
            }
        }

        return call_user_func($transform[0], $value);

    }

}