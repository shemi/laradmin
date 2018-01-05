<?php

namespace Shemi\Laradmin\Contracts\Repositories;

use Illuminate\Database\Eloquent\Model;
use Shemi\Laradmin\Models\Type;

interface CreateUpdateRepository
{

    public function createOrUpdate($data, Model $model, Type $type);

    public function saved();

    public function failed();

    public function errors();

    public function hasErrors();

    public function warnings();

    public function hasWarnings();

}