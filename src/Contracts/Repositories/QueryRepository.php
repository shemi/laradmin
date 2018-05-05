<?php

namespace Shemi\Laradmin\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Shemi\Laradmin\Models\Type;

interface QueryRepository
{

    /**
     * @param Request $request
     * @param Type $type
     * @return Collection|LengthAwarePaginator
     */
    public static function query(Request $request, Type $type);

}