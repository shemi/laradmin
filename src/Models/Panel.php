<?php

namespace Shemi\Laradmin\Models;


use Shemi\Laradmin\Data\Collection;
use Shemi\Laradmin\Data\Model;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Shemi\Laradmin\FormPanels\FormPanel;
use Shemi\Laradmin\Managers\FormPanelsManager;
use Shemi\Laradmin\Models\Contracts\Buildable as BuildableContract;
use Shemi\Laradmin\Models\Traits\Buildable;
use Shemi\Laradmin\Transformers\Builder\PanelTransformer;

/**
 * Shemi\Laradmin\Models\Type
 *
 * @property integer $id
 * @property string $title
 * @property string $position
 * @property string $object_type
 * @property boolean $is_main_meta
 * @property boolean $has_container
 * @property boolean $is_supporting_fields_labels
 * @property array $style
 * @property Collection $fields
 */
class Panel extends Model implements BuildableContract
{

    use Buildable;

    const OBJECT_TYPE = 'panel';

    /**
     * @var bool $dataable
     */
    protected $dataable = false;

    /**
     * @var Collection $_fields
     */
    protected $_fields;

    /**
     * @var Type $_type
     */
    protected $_type;

    /**
     * @var string $keyType
     */
    protected $keyType = 'string';

    /**
     * @var array $fillable
     */
    protected $fillable = [
        'id',
        'title',
        'position',
        'type',
        'is_main_meta',
        'has_container',
        'style',
        'fields',
        'object_type'
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

    public function getIsSupportingFieldsLabelsAttribute()
    {
        return $this->formPanel()->isSupportingFieldsLabels();
    }

    public static function isValidPanel($panel)
    {
        if ($panel instanceof Panel) {
            return true;
        }

        if(isset($panel['object_type'])) {
            return $panel['object_type'] === static::OBJECT_TYPE;
        }

        if (is_array($panel) && array_get($panel, 'type') === 'repeater') {
            return false;
        }

        return is_array($panel) &&
            array_key_exists('fields', $panel) &&
            ! empty($panel['fields']);
    }

    /**
     * @param $rawPanel
     * @param Model $type
     * @return mixed
     */
    public static function fromArray($rawPanel, Model $type)
    {
        /** @var Panel $inst */
        $inst = (new static)->newFromManager($rawPanel);

        $inst->fields = new Collection((array) $inst->fields);

        $inst->fields = $inst->fields
            ->reject(function ($field) {
                return ! Field::isValidField($field) &&
                       ! static::isValidPanel($field);
            })
            ->transform(function ($field) use ($inst, $type) {
                if (static::isValidPanel($field)) {
                    return static::fromArray($field, $type);
                }

                return Field::fromArray($field, $inst, $type);
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

    public function fieldsFor($view = "any")
    {
        if($view === "any") {
            return $this->fields->values();
        }

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
     * @param Model $type
     * @param $viewType
     * @param array $data
     * @return string
     * @throws \Throwable
     */
    public function render(Model $type, $viewType, $data)
    {
        return $this->formPanel()->handle($this, $type, $viewType, $data);
    }

    public function toBuilder()
    {
        return $this->builderMode(function () {
            return PanelTransformer::transform($this);
        });
    }

    /**
     * @param mixed $type
     * @return Panel
     */
    public function setType(Model $type)
    {
        $this->_type = $type;

        return $this;
    }

    /**
     * @return Model
     */
    public function getType(): Model
    {
        return $this->_type;
    }

}