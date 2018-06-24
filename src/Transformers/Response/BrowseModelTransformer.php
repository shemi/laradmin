<?php

namespace Shemi\Laradmin\Transformers\Response;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Shemi\Laradmin\Contracts\HasMediaContract;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Transformers\FieldDefaultValueTransformer;
use Spatie\MediaLibrary\Media;

class BrowseModelTransformer extends Transformer
{
    /**
     * @var Collection $fields
     */
    protected $fields;

    /**
     * @var Model $model
     */
    protected $model;


    /**
     * @param Collection $fields
     * @param Model $model
     * @return array
     * @throws \Exception
     */
    public function transform(Collection $fields, Model $model)
    {
        $this->fields = $fields;
        $this->model = $model;
        $data = [
            $model->getKeyName() => $model->getKey()
        ];

        /** @var Field $field */
        foreach ($fields as $field) {
            array_set(
                $data,
                $field->browse_key,
                (new BrowseFieldTransformer)->transform($field, $model)
            );
        }

        return $data;
    }


}