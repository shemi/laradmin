<?php

namespace Shemi\Laradmin\Contracts\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Shemi\Laradmin\Models\Type;

interface TypeModelQueryRepository extends Repository
{

    public function find($id, Type $type, Collection $fields = null);

    public function setType(Type $type);

    public function setFields(Collection $fields);

    public function setModel(Model $model);

}