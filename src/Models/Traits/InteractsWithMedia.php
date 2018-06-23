<?php

namespace Shemi\Laradmin\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Shemi\Laradmin\Models\SettingsPage;
use Spatie\MediaLibrary\Media;

/**
 * Shemi\Laradmin\Models\Traits\FieldHasMedia
 *
 * @property int $id
 * @property array|null $relationship
 * @property array $media
 * @property boolean $is_media
 * @property boolean $is_single_media
 * @property array $media_disk
 * @property boolean $is_relationship
 * @property string $key
 * @property string $type
 * @property string $media_collection
 */
trait InteractsWithMedia
{

    public static $mediaTypes = ['images', 'files', 'file', 'image'];

    public static $singleMediaTypes = ['file', 'image'];

    public static $defaultMediaCollection = "default";

    public function getIsMediaAttribute()
    {
        return in_array($this->type, static::$mediaTypes);
    }

    public function getIsSingleMediaAttribute()
    {
        return in_array($this->type, static::$singleMediaTypes);
    }

    public function getMediaAttribute($value)
    {
        if(! $value || ! is_array($value)) {
            return [
                'disk' => config('medialibrary.defaultFilesystem')
            ];
        }

        if(! array_key_exists('disk', $value) || ! $value['disk']) {
            $value['disk'] = config('medialibrary.defaultFilesystem');
        }

        return $value;
    }

    public function getMediaDisKAttribute()
    {
        return $this->media['disk'];
    }

    public function getMediaCollectionAttribute()
    {
        if($this->getType() instanceof SettingsPage && ! $this->is_sub_field) {
            return static::$defaultMediaCollection;
        }

        if($this->is_sub_field && ! $this->parent->is_relationship) {
            return "{$this->parent->media_collection}.{$this->key}";
        }
        
        return $this->key;
    }

    public function getMediaParentModel(Model $model)
    {

    }

}