<?php

namespace Shemi\Laradmin\Exceptions\SyncMedia;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;

class ModelMustImplementHasMediaException extends SyncMediaException
{

    /**
     * @param Model $model
     * @param Exception $original
     * @return static
     */
    public static function create(Model $model, Exception $original = null)
    {
        $modelClass = get_class($model);
        $hasMediaContract = HasMedia::class;

        $message = "The model: \"{$modelClass}\" must implement: \"{$hasMediaContract}\"";

        return new static($message, $original);
    }

}