<?php
namespace Shemi\Laradmin\FormFields;

use Illuminate\Database\Eloquent\Model;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;

interface FieldContract
{
    public function handle(Field $field, Type $type, Model $model, $data);

    public function createContent(Field $field, Type $type, Model $model, $data);

    public function getCodename();

    public function getName();

}