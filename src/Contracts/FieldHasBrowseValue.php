<?php

namespace Shemi\Laradmin\Contracts;

use Illuminate\Database\Eloquent\Model;
use Shemi\Laradmin\Models\Field;

interface FieldHasBrowseValue
{
    public function renderBrowseValue(Field $field, Model $model);

}