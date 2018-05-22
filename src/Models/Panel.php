<?php

namespace Shemi\Laradmin\Models;


use Shemi\Laradmin\Data\Collection;
use Shemi\Laradmin\Data\Model;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Shemi\Laradmin\FormPanels\FormPanel;
use Shemi\Laradmin\Managers\FormPanelsManager;

/**
 * Shemi\Laradmin\Models\Type
 *
 * @property integer $id
 * @property string $title
 * @property string $position
 * @property boolean $is_main_meta
 * @property boolean $has_container
 * @property array $style
 * @property Collection $fields
 */
class Panel extends Model
{
    protected $dataable = false;

    protected $_fields;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'title',
        'position',
        'type',
        'is_main_meta',
        'has_container',
        'style',
        'fields'
    ];

    public function getTypeAttribute($value)
    {
        if(! $value) {
            return $this->is_main_meta ? 'main_meta' : 'simple';
        }

        return $value;
    }

    public function getTabsAttribute($value)
    {
        return (array) $value ?: [];
    }

    public function getStyleAttribute($value)
    {
        if (is_string($value)) {
            return $value;
        }

        $style = (object) $value ? $value : [];

        return json_encode($style, JSON_UNESCAPED_UNICODE);
    }

    public function getHasContainerAttribute($value)
    {
        return $value === null ? true : $value;
    }

    public static function isValidPanel($panel)
    {
        if ($panel instanceof Panel) {
            return true;
        }

        if (is_array($panel) && array_get($panel, 'type') === 'repeater') {
            return false;
        }

        return is_array($panel) &&
            array_key_exists('fields', $panel) &&
            ! empty($panel['fields']);
    }

    public static function fromArray($rawField)
    {
        $inst = (new static)->newFromManager($rawField);

        $inst->fields = new Collection((array) $inst->fields);

        $inst->fields = $inst->fields
            ->reject(function ($field) {
                return ! Field::isValidField($field) &&
                       ! static::isValidPanel($field);
            })
            ->transform(function ($field) {
                if (static::isValidPanel($field)) {
                    return static::fromArray($field);
                }

                return Field::fromArray($field);
            });

        return $inst;
    }

    public function getFieldsAttribute($value)
    {
        return $value;
    }

    public function flatFields()
    {
        $fields = new Collection([]);

        foreach ($this->fields as $field) {
            if ($field instanceof Panel) {
                $fields->merge($field->flatFields());
            } elseif ($field instanceof Field) {
                $fields->push($field);
            }
        }

        return $fields;
    }

    public function fieldsFor($view)
    {
        return $this->fields
            ->reject(function ($field) use ($view) {
                return ! $field->isVisibleOn($view);
            })
            ->values();
    }

    /**
     * @return FormPanelsManager
     */
    protected function formPanelsManager()
    {
        return app('laradmin')->formPanels();
    }

    /**
     * @return FormPanel
     */
    public function formPanel()
    {
        return $this->formPanelsManager()
            ->panel($this->type);
    }

    /**
     * @param Type $type
     * @param EloquentModel $model
     * @param $viewType
     * @param array $data
     * @return string
     * @throws \Throwable
     */
    public function render(Type $type, EloquentModel $model, $viewType, $data)
    {
        return $this->formPanel()->handle($this, $type, $model, $viewType, $data);
    }

    public function toBuilder()
    {
        $fields = [
            'id',
            'title',
            'type',
            'position',
            'has_container',
            'style',
            'tabs'
        ];

        $array = [];

        foreach ($fields as $key) {
            $array[$key] = $this->{$key};
        }

        if ($this->fields->isNotEmpty()) {
            $array['fields'] = $this->fields->map(function ($field) {
                return $field->toBuilder();
            });
        } else {
            $array['fields'] = (array) [];
        }

        return $array;
    }

}