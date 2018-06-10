<?php

namespace Shemi\Laradmin\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use Shemi\Laradmin\Contracts\Repositories\ComplexFieldValueTransformerRepository;
use Shemi\Laradmin\Exceptions\CreateUpdateRelationModelNotFoundException;
use Shemi\Laradmin\Exceptions\CreateUpdateTransformCantFindCopyFieldOrAttributeException;
use Shemi\Laradmin\Exceptions\CreateUpdateUnableToSaveModelException;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Setting;
use Shemi\Laradmin\Models\SettingsPage;
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
            $this->syncMedia();

            event('laradmin::before-save-settings', $this->models, $this->page);

            $this->save();

            event('laradmin::after-settings-saved', $this->models, $this->page);
        });

        return $this;
    }

    protected function setModels()
    {
        $this->models = Setting::where('bucket', $this->page->bucket)
            ->get();

        // add missing models
        foreach ($this->fields as $field) {
            $this->getModel($field);
        }
    }

    protected function getModel(Field $field)
    {
        $model = $this->models
            ->where('key', $field->key)
            ->first();

        if($model) {
            return $model;
        }

        $model = new Setting([
            'key' => $field->key,
            'bucket' => $this->page->bucket,
            'type' => $field->setting_type
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
            $model = $this->getModel($field);
            $value = data_get($this->data, $field->key);

            if($model->exists && $field->is_password && ! $value) {
                return;
            }

            $value = $this->getFieldValue($field);

            if($field->is_support_sub_fields) {
                $value = $this->getComplexRepo()
                    ->transform($value, $field, $model, "value");
            }

            $model->setAttribute("value", $value);
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

            elseif ($field->is_support_sub_fields && ($field->has_relationship_type || isset($field->relationship['model']))) {
                $type = $field->relationship_type ?: null;
                $model = app($type ?: $field->relationship['model']);
                
                if($id = array_get($value, $model->getKeyName())) {
                    $model = $model->find($id);
                }

                app(CreateUpdateRepository::class)
                    ->createOrUpdate($value, $model, $type, $field->getSubFields());

                $this->getModel($field)->setAttribute("value", $model->getKey());
            }

            else {
                $this->getModel($field)->setAttribute("value", $value);
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
        $setting = $this->getModel($field);
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

            $this->getCreateUpdateRepo()
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

    /**
     * @throws CreateUpdateTransformCantFindCopyFieldOrAttributeException
     * @throws \Shemi\Laradmin\Exceptions\SyncMedia\SyncMediaException
     */
    public function syncMedia()
    {
        /** @var Field $field */
        foreach ($this->mediaFields as $field) {
            $this->getSyncMediaRepo()
                ->sync(
                    $this->getFieldValue($field),
                    $this->getModel($field),
                    $field
                );
        }
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

    /**
     * @return CreateUpdateRepository
     */
    protected function getCreateUpdateRepo()
    {
        return app(CreateUpdateRepository::class)->fresh();
    }

    /**
     * @return ComplexFieldValueTransformerRepository
     */
    protected function getComplexRepo()
    {
        return app(ComplexFieldValueTransformerRepository::class)->fresh();
    }

    /**
     * @return SyncMediaRepository
     */
    protected function getSyncMediaRepo()
    {
        return app(SyncMediaRepository::class)->fresh();
    }

    public function fresh()
    {
        return new static;
    }

}
