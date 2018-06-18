<?php

namespace Shemi\Laradmin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Shemi\Laradmin\Contracts\HasMediaConversionsContract;
use Shemi\Laradmin\Traits\HasMedia;
use Spatie\MediaLibrary\Media;

class Setting extends Model implements HasMediaConversionsContract
{
    use HasMedia;

    const TYPE_STRING = 'string';
    const TYPE_TIMESTAMP = 'timestamp';
    const TYPE_DATE = 'date';
    const TYPE_TIME = 'datetime';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_INT = 'int';
    const TYPE_FLOAT = 'float';
    const TYPE_NUMERIC = 'numeric';
    const TYPE_OBJECT = 'object';
    const TYPE_ARRAY = 'array';
    const TYPE_SINGLE_MEDIA = 'single_media';
    const TYPE_MEDIA = 'media';
    const TYPE_SINGLE_RELATIONSHIP = 'single_relationship';
    const TYPE_RELATIONSHIP = 'relationship';

    protected $table = "la_settings";

    protected $_value;

    protected $fillable = [
        'key',
        'value',
        'type',
        'bucket'
    ];


    protected $casts = [
        'encrypted' => 'boolean',
        'value' => 'json'
    ];

    public function getValueAttribute($value)
    {
        if($this->_value) {
            return $this->_value;
        }

        $type = $this->type;

        if (! $value && ! in_array($type, [static::TYPE_SINGLE_MEDIA, static::TYPE_MEDIA])) {
            return null;
        }

        if ($type === static::TYPE_NUMERIC) {
            $type = static::TYPE_INT;

            if (is_float($value) || str_contains($value, ".")) {
                $type = static::TYPE_FLOAT;
            }
        }

        $field = $this->getSettingPageField();

        switch ($type) {
            case 'int':
            case 'integer':
                $value = (int) $this->fromJson($value);
                break;
            case 'real':
            case 'float':
            case 'double':
                $value = (float) $this->fromJson($value);
                break;
            case 'string':
                $value = (string) $this->fromJson($value);
                break;
            case 'bool':
            case 'boolean':
                $value = (bool) $this->fromJson($value);
                break;
            case 'object':
            case 'array':
            case 'json':
                $value = $this->fromJson($value);
                break;
            case 'date':
                $value = $this->asDate($this->fromJson($value));
                break;
            case 'datetime':
                $value = $this->asDateTime($this->fromJson($value));
                break;
            case 'timestamp':
                $value = $this->asTimestamp($this->fromJson($value));
                break;
            case static::TYPE_SINGLE_RELATIONSHIP:
            case static::TYPE_RELATIONSHIP:
                if($field) {
                    /** @var Model $model */
                    $model = app(
                        $field->relationship_type ?
                            $field->relationship_type->model :
                            $field->relationship['model']
                    );

                    $value = $model->whereIn($field->getRelationKeyName($model), (array) $this->fromJson($value))
                        ->get();

                    if($type === static::TYPE_SINGLE_RELATIONSHIP && $value instanceof Collection) {
                        $value = $value->first();
                    }
                }

                break;
        }

        $this->_value = $value;

        return $this->_value;
    }

    /**
     * @return null|Field
     */
    protected function getSettingPageField()
    {
        $pages = SettingsPage::where('bucket', $this->bucket);

        /** @var SettingsPage $page */
        foreach ($pages as $page) {

            $field = $page->fields
                ->where('key', $this->key)
                ->first();

            if($field) {
                return $field;
            }
        }

        return null;
    }

    public function registerMediaConversions(Media $media = null)
    {

    }
}