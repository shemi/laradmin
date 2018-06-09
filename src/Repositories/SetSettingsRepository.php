<?php

namespace Shemi\Laradmin\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use Shemi\Laradmin\Exceptions\CreateUpdateRelationModelNotFoundException;
use Shemi\Laradmin\Exceptions\CreateUpdateTransformCantFindCopyFieldOrAttributeException;
use Shemi\Laradmin\Exceptions\CreateUpdateDeleteMediaException;
use Shemi\Laradmin\Exceptions\CreateUpdateMediaModelNotFoundException;
use Shemi\Laradmin\Exceptions\CreateUpdateUnableToClearMediaException;
use Shemi\Laradmin\Exceptions\CreateUpdateUnableToSaveMediaException;
use Shemi\Laradmin\Exceptions\CreateUpdateUnableToSaveModelException;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Setting;
use Shemi\Laradmin\Models\SettingsPage;
use Spatie\MediaLibrary\Media;
use Shemi\Laradmin\Contracts\Repositories\CreateUpdateRepository;

class SetSettingsRepository
{

    /**
     * @var array $data
     */
    protected $data;

    /**
     * @var SettingsPage $page
     */
    protected $page;

    /**
     * @var Collection $fields
     */
    protected $fields;

    /**
     * @var Collection $mediaFields
     */
    protected $mediaFields;

    /**
     * @var Collection $relationFields
     */
    protected $relationFields;

    /**
     * @var Collection $modelFields
     */
    protected $modelFields;

    /**
     * @var EloquentCollection $models
     */
    protected $models;

    public function __construct()
    {
        $this->mediaFields = Collection::make([]);
        $this->relationFields = Collection::make([]);
        $this->modelFields = Collection::make([]);
    }

    /**
     * @param array $data
     * @param SettingsPage $page
     * @param Collection|null $fields
     *
     * @return SetSettingsRepository
     */
    public function set($data, SettingsPage $page, Collection $fields = null)
    {
        $this->page = $page;
        $this->data = $data;

        $this->setFields($fields);
        $this->setModels();

        DB::transaction(function () {
            $this->setRegularFields();
            $this->syncRelationData();

            event('laradmin::before-save-settings', $this->models, $this->page);

            $this->save();

            event('laradmin::after-settings-saved', $this->models, $this->page);

            $this->syncMedia();
        });

        return $this;
    }

    protected function setModels()
    {
        $this->models = Setting::where('bucket', $this->page->bucket)
            ->get();

        // add missing/new models
        foreach ($this->fields as $field) {
            $this->getModel($field->key);
        }
    }

    protected function getModel($key)
    {
        $model = $this->models
            ->where('key', $key)
            ->first();

        if($model) {
            return $model;
        }

        $model = new Setting([
            'key' => $key,
            'bucket' => $this->page->bucket
        ]);

        $this->models->add($model);

        return $model;
    }

    protected function setFields(Collection $fields = null)
    {

        if($fields) {
            $this->fields = $fields;
        } else {
            $this->fields = $this->page->fields;
        }

        $this->fields->each(function(Field $field) {

            if($field->read_only) {
                return;
            }

            if($field->is_media) {
                $this->mediaFields->push($field);
            }

            elseif ($field->is_relationship) {
                if(! $field->has_relationship_type && ! isset($field->relationship['model'])) {
                    throw CreateUpdateRelationModelNotFoundException::create($field->key);
                }

                $this->relationFields->push($field);
            }

            else {
                $this->modelFields->push($field);
            }
        });

        return $this;
    }

    protected function setRegularFields()
    {
        $this->modelFields->each(function(Field $field) {
            $model = $this->getModel($field->key);
            $value = data_get($this->data, $field->key);

            if($model->exists && $field->is_password && ! $value) {
                return;
            }

            $model->setAttribute("value", $this->getFieldValue($field));
        });

        return $this;
    }

    public function syncRelationData()
    {
        $this->relationFields->each(function(Field $field) {
            $value = $this->getFieldValue($field);

            if($field->type === 'repeater' && $field->has_relationship_type) {
                $this->createUpdateDeleteRepeaterRows($field, $value);
            }

            elseif ($field->has_relationship_type && $field->is_support_sub_fields) {
                $type = $field->relationship_type;
                $model = app($type->model);
                
                if($id = array_get($value, $model->getKeyName())) {
                    $model = $model->find($id);
                }

                app(CreateUpdateRepository::class)
                    ->createOrUpdate($value, $model, $type, $field->getSubFields());

                $this->getModel($field->key)->setAttribute("value", $model->getKey());
            }

            else {
                $this->getModel($field->key)->setAttribute("value", $value);
            }

        });

        return $this;
    }

    /**
     * @param Field $field
     * @param $rows
     * @return void
     */
    protected function createUpdateDeleteRepeaterRows(Field $field, $rows)
    {
        $type = $field->relationship_type;

        /** @var Model $model */
        $model = app($type->model);
        $primaryKey = $model->getKeyName();

        $rows = Collection::make($rows);
        $setting = $this->getModel($field->key);
        $currentRows = $setting->value;

        if(empty($currentRows) || ! $currentRows instanceof Collection) {
            $currentRows = collect([]);
        }

        $newIds = [];

        foreach ($rows as $row) {
            $id = $row[$primaryKey];
            $exists = $currentRows->where($primaryKey, $id)->first();

            if($exists) {
                $model = $exists;
            } else {
                $model = app($type->model);
            }

            app(CreateUpdateRepository::class)
                ->createOrUpdate($row, $model, $type, $field->getSubFields());

            $newIds[] = $model->getKey();
        }

        $setting->setAttribute("value", $newIds);
    }

    /**
     * @return bool
     * @throws CreateUpdateUnableToSaveModelException
     */
    public function save()
    {

        try {
            $this->models->each(function(Model $model) {
                /** @var Field $field */
                $field = $this->fields->where('key', $model->key)->first();
                $model->setAttribute('type', $field->setting_type);

                $model->saveOrFail();
            });
        }

        catch (\Throwable $exception) {
            throw CreateUpdateUnableToSaveModelException::create("", $exception);
        }

        return true;
    }

    public function syncMedia()
    {
        $this->mediaFields->each(function(Field $field) {
            /** @var Collection $media */
            $media = $this->getFieldValue($field);
            $model = $this->getModel($field->key);

            /** @var Collection $modelMedia */
            $modelMedia = $model->getMedia();

            if($media->isEmpty() && $modelMedia->isNotEmpty()) {

                try {
                    $model->clearMediaCollection();
                }
                catch (\Exception $exception) {
                    throw CreateUpdateUnableToClearMediaException::create($exception);
                }

                return;
            }

            $toUpdate = $media->reject(function($media) {
                return $media->is_new;
            });

            $toInsert = $media->reject(function($media) {
                return ! $media->is_new;
            });


            if($modelMedia->isNotEmpty()) {
                $modelMedia = $this->deleteUnusedMedia($modelMedia, $toUpdate, $field);
            }

            if($toUpdate->isNotEmpty()) {
                $this->updateMedia($toUpdate, $modelMedia);
            }

            if($toInsert->isNotEmpty()) {
                $this->insertMedia($toInsert, $field);
            }

        });
    }

    /**
     * @param Collection $modelMedia
     * @param Collection $toUpdate
     * @param Field $field
     * @return Collection
     */
    protected function deleteUnusedMedia(Collection $modelMedia, Collection $toUpdate, Field $field)
    {
        return $modelMedia->reject(function($media) use ($toUpdate, $field) {
            if(! $toUpdate->pluck('id')->contains($media->id)) {
                try {
                    $media->delete();
                }

                catch (\Exception $exception) {
                    throw CreateUpdateDeleteMediaException::create($media->id, $field->key, $exception);
                }

                return true;
            }

            return false;
        });
    }

    /**
     * @param Collection $toUpdate
     * @param Collection $modelMediaCollection
     * @return $this
     */
    protected function updateMedia(Collection $toUpdate, Collection $modelMediaCollection)
    {
        $toUpdate->each(function($media) use ($modelMediaCollection) {

            $mediaModel = $modelMediaCollection->first(function($mediaModel) use ($media) {
                return $media->id === $mediaModel->id;
            });

            if(! $mediaModel) {
                throw CreateUpdateMediaModelNotFoundException::create();
            }

            $mediaModel->name = $media->name ?: $mediaModel->name;
            $mediaModel->order_column = $media->order;
            $mediaModel->setCustomProperty('alt', $media->alt);
            $mediaModel->setCustomProperty('caption', $media->caption);

            $this->saveMediaModel($mediaModel);
        });

        return $this;
    }

    /**
     * @param Collection $toInsert
     * @param Field $field
     * @return $this
     */
    protected function insertMedia(Collection $toInsert, Field $field)
    {
        $model = $this->getModel($field->key);

        $toInsert->each(function($media) use ($field, $model) {

            try {
                $mediaModel = $model
                    ->addMedia(storage_path('app/'.$media->temp_path))
                    ->usingName($media->name)
                    ->withCustomProperties([
                        'alt' => $media->alt,
                        'caption' => $media->caption
                    ])
                    ->toMediaCollection('default', $field->media_disk);
            }
            catch (\Throwable $exception) {
                throw CreateUpdateUnableToSaveMediaException::create(
                    $media->name,
                    $field->key,
                    $field->media_disk,
                    $exception
                );
            }

            $mediaModel->order_column = $media->order;
            $this->saveMediaModel($mediaModel);
        });

        return $this;
    }

    /**
     * @param Media $media
     * @return bool
     * @throws CreateUpdateUnableToSaveMediaException
     */
    protected function saveMediaModel(Media $media)
    {
        try {
            $media->saveOrFail();
        }

        catch (\Throwable $exception) {
            throw CreateUpdateUnableToSaveMediaException::create(
                $media->name,
                $media->collection_name,
                $media->disk,
                $exception
            );
        }

        return true;
    }

    /**
     * @param Field $field
     * @return mixed
     * @throws CreateUpdateTransformCantFindCopyFieldOrAttributeException
     */
    protected function getFieldValue(Field $field)
    {
        return $this->transformValue(
            $field->transformRequest(data_get($this->data, $field->key)),
            $field
        );
    }

    /**
     * @param $value
     * @param Field $field
     * @return mixed
     * @throws CreateUpdateTransformCantFindCopyFieldOrAttributeException
     */
    protected function transformValue($value, Field $field)
    {
        $transform = explode(':', $field->getTemplateOption('transform', 'value'));

        if(! $value && count($transform) > 1) {
            $copyKey = $transform[1];

            $copyField = $this->fields
                ->where('key', $copyKey)
                ->first();

            if($copyField) {
                $value = $this->getFieldValue($copyField);
            }

            else {
                throw CreateUpdateTransformCantFindCopyFieldOrAttributeException::create($copyKey);
            }
        }

        return call_user_func($transform[0], $value);
    }

}
