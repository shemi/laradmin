<?php

namespace Shemi\Laradmin\Contracts\Repositories;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Shemi\Laradmin\Exceptions\SyncMedia\SyncMediaException;
use Shemi\Laradmin\Models\Field;

interface SyncMediaRepository extends Repository
{

    /**
     * @param $new
     * @param Model $model
     * @param Field $field
     * @return mixed
     * @throws SyncMediaException
     */
    public function sync($new, Model $model, Field $field);

    /**
     * @return array
     */
    public function getCurrentIds();

}