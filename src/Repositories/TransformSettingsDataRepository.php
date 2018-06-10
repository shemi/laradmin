<?php

namespace Shemi\Laradmin\Repositories;


use Illuminate\Database\Eloquent\Collection;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Setting;
use Shemi\Laradmin\Models\SettingsPage;

class TransformSettingsDataRepository
{

    /**
     * @var Collection $settings
     */
    protected $settings;

    /**
     * @var SettingsPage $page
     */
    protected $page;

    public function transform(SettingsPage $page)
    {
        $this->page = $page;
        $this->settings = Setting::where('bucket', $page->bucket)
            ->with(['media'])
            ->get();

        return $this->getTransformedData();
    }

    protected function getTransformedData()
    {
        $data = [];

        /** @var Field $field */
        foreach ($this->page->fields as $field) {
            $data[$field->key] = $field->getModelValue($this->getSettingModel($field->key), "value");
        }

        return $data;
    }

    protected function getSettingModel($key)
    {
        $model = $this->settings
            ->where('key', $key)
            ->first();

        return $model ?: new Setting(['bucket' => $this->page->bucket, 'key' => $key]);
    }

    public function fresh()
    {
        return new static;
    }

}
