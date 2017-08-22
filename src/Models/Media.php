<?php

namespace Shemi\Laradmin\Models;

use Spatie\MediaLibrary\Media as BaseMedia;

class Media extends BaseMedia
{

    public function getNameAttribute($value)
    {
        if(! pathinfo($value, PATHINFO_EXTENSION)) {
            $value .= ".{$this->extension}";
        }

        return $value;
    }

}