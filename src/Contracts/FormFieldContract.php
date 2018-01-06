<?php
namespace Shemi\Laradmin\Contracts;

use Illuminate\Database\Eloquent\Model;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;

interface FormFieldContract
{
    public function handle(Field $field, Type $type, Model $model, $data);

    public function createContent(Field $field, Type $type, Model $model, $data);

    public function getCodename();

    public function getName();

    public function transformRequest(Field $field, $data);

    public function transformResponse(Field $field, $data);

}