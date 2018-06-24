<?php

namespace Shemi\Laradmin\Contracts\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Shemi\Laradmin\Models\Type;

interface TransformTypeModelDataRepository extends Repository
{

    public function transform(Type $type, Model $model = null, Collection $fields = null);

}