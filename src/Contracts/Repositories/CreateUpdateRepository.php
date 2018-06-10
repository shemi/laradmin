<?php

namespace Shemi\Laradmin\Contracts\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Shemi\Laradmin\Models\Type;

interface CreateUpdateRepository extends Repository
{

    public function createOrUpdate($data, Model $model, Type $type = null, Collection $fields = null);

}