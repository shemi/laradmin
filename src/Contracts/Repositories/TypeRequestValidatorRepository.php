<?php

namespace Shemi\Laradmin\Contracts\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Shemi\Laradmin\Models\Type;

interface TypeRequestValidatorRepository extends Repository
{

    public function validate(Request $request, Type $type,
                             Model $model, Collection $fields = null,
                             $throw = true);

}