<?php

namespace Shemi\Laradmin\Models\Traits;

use Illuminate\Support\Collection;
use Shemi\Laradmin\Models\Media;

/**
 * Shemi\Laradmin\Models\Traits\FieldHasMedia
 *
 * @property int $id
 * @property array|null $relationship
 * @property array $media
 * @property array $media_disc
 * @property boolean $is_relationship
 * @property string $key
 * @property string $type
 */
trait InteractsWithMedia
{

    public function getIsMediaAttribute()
    {
        return in_array($this->type, ['images', 'files', 'file', 'image']);
    }

    public function getIsSingleMediaAttribute()
    {
        return in_array($this->type, ['file', 'image']);
    }

    public function getMediaAttribute($value)
    {
        if(! $value || ! is_array($value)) {
            return [
                'disc' => config('medialibrary.defaultFilesystem')
            ];
        }

        if(! array_key_exists('disc', $value) || ! $value['disc']) {
            $value['disc'] = config('medialibrary.defaultFilesystem');
        }

        return $value;
    }

    public function getMediaDiscAttribute()
    {
        return $this->media['disc'];
    }

    public function transformMediaCollection(Collection $collection)
    {
        return $collection->transform(function(Media $media) {
            return $this->transformMediaModel($media);
        })->toArray();
    }

    public function transformMediaModel(Media $media)
    {
        return [
            'id' => $media->id,
            'name' => $media->name,
            'size' => $media->size,
            'ext' => $media->extension,
            'uri' => route('laradmin.serve', [
                'mediaId' => $media->id,
                'fileName' => $media->name
            ]),
            'alt' => $media->getCustomProperty('alt'),
            'caption' => $media->getCustomProperty('caption'),
        ];
    }

}