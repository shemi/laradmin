<?php

namespace Shemi\Laradmin\Transformers\Response;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Shemi\Laradmin\Contracts\HasMediaContract;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Transformers\Response\Transformer;
use Spatie\MediaLibrary\Media;

class MediaTransformer extends Transformer
{
    /**
     * @var Field $field
     */
    protected $field;

    /**
     * @var Model $model
     */
    protected $model;

    /**
     * @param Field $field
     * @param Model $model
     * @param bool $internal
     * @return array|null|Media|Collection
     * @throws \Exception
     * @throws \Shemi\Laradmin\Exceptions\ManagerDoesNotExistsException
     */
    public function transform(Field $field, Model $model, $internal = true)
    {
        $this->field = $field;
        $this->model = $model;

        if(! $model instanceof HasMediaContract) {
            throw new \Exception("The model must implements \"".HasMediaContract::class."\"");
        }

        $value = $this->model->getMedia($this->field->media_collection);

        if(! $internal) {
            return $this->expectCollection() ? $value : $value->first();
        }

        if(! $this->expectCollection()) {
            return $value->isEmpty() ? null : $this->asMediaModel($value->first());
        }

        return $this->asMediaCollection($value);
    }

    protected function expectCollection()
    {
        if(! $this->field->is_single_media) {
            return true;
        }

        if($this->field->parent && $this->field->parent->is_relationship) {
            return false;
        }

        if($this->field->is_repeater_sub_field) {
            return true;
        }

        return false;
    }

    /**
     * @param Collection $collection
     * @return array
     */
    public function asMediaCollection(Collection $collection)
    {
        return $collection->transform(function(Media $media) {
            return $this->asMediaModel($media);
        })->toArray();
    }

    /**
     * @param Media $media
     * @return array
     * @throws \Shemi\Laradmin\Exceptions\ManagerDoesNotExistsException
     */
    public function asMediaModel(Media $media)
    {
        return [
            'id' => $media->id,
            'name' => $media->name,
            'size' => $media->size,
            'ext' => $media->extension,
            'uri' => laradmin('links')->serveMedia($media->id, $media->name),
            'alt' => $media->getCustomProperty('alt'),
            'caption' => $media->getCustomProperty('caption'),
        ];
    }

}