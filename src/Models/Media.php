<?php

namespace Shemi\Laradmin\Models;

use League\Flysystem\Util\MimeType;
use Spatie\MediaLibrary\Media as BaseMedia;

class Media extends BaseMedia
{

    public function getNameAttribute($value)
    {
        $userExt = pathinfo($value, PATHINFO_EXTENSION);

        if(! $userExt || $userExt !== $this->extension) {
            $value .= ".{$this->extension}";
        }

        return $value;
    }

}