<?php

namespace Shemi\Laradmin\Transformers\Response;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Shemi\Laradmin\Contracts\FieldHasBrowseValue;
use Shemi\Laradmin\Models\Field;

class BrowseFieldTransformer extends Transformer
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
     * @var array $parentModels
     */
    protected $parentModels = [];

    /**
     * @var Collection $parents
     */
    protected $parents;

    /**
     * @param Field $field
     * @param Model $model
     * @return array
     * @throws \Exception
     */
    public function transform(Field $field, Model $model)
    {
        $this->parents = collect([]);
        $this->parentModels = [];
        $this->field = clone $field;
        $this->model = clone $model;

        if(laradmin()->formFields()->exists($field->type)) {
            $formField = $field->formField();

            if($formField instanceof FieldHasBrowseValue) {
                return $formField->renderBrowseValue($field, $model);
            }
        }

        while ($field->is_sub_field && $field->parent) {
            $field = $field->parent;
            $this->parents->push($field);

            if($field->is_relationship) {
                $model = $field->getRelationModelClass($model);
                $this->parentModels[$field->key] = $model;
            } else {
                $model = null;
            }
        }

        if(in_array($this->field->type, ['select', 'radio'])) {
            return $this->asSingleChoice();
        }

        if($this->field->isDate()) {
            return $this->asDate();
        }

        if($this->field->is_media) {
            return $this->asMedia();
        }

        if(in_array($this->field->type, ['checkbox', 'switch'])) {
            return $this->asBoolean();
        }

        if($this->field->is_relationship) {
            return $this->asMultipleChoices();
        }

        return $this->getValue();
    }

    protected function asSingleChoice()
    {
        $value = $this->getValue();

        if(! $this->field->is_relationship || ! $value instanceof Model) {
            return $value;
        }

        return [$this->relationLabels($value)];
    }

    protected function asMultipleChoices()
    {
        $value = $this->getValue();

        if(! $this->field->is_relationship) {
            return $value;
        }

        if(! $value || ! $value instanceof Collection) {
            return 'Relation error!';
        }

        return $value
            ->map(function(Model $model) {
                return $this->relationLabels($model);
            })
            ->all();
    }

    protected function relationLabels(Model $model, $keys = null)
    {
        $keys = $keys ?: $this->field->relation_labels;

        $labels = [];

        foreach ($keys as $label) {
            $labels[] = $model->getAttribute($label);
        }

        return $labels;
    }

    protected function asDate()
    {
        $value = $this->getValue();

        if(is_array($value) && array_key_exists('date', $value)) {
            $value = Carbon::parse($value['date'], 'UTC');
        }

        if(is_string($value)) {
            $value = Carbon::parse($value);
        }

        if(! $value instanceof Carbon) {
            return null;
        }

        $value->timezone('UTC');

        return $value->toIso8601String();
    }

    /**
     * @return array|Collection|null|\Spatie\MediaLibrary\Media
     * @throws \Exception
     * @throws \Shemi\Laradmin\Exceptions\ManagerDoesNotExistsException
     */
    protected function asMedia()
    {
        $model = $this->model;

        if($this->parents->isNotEmpty()) {
            /** @var Field $parent */
            foreach ($this->parents as $parent) {
                if($this->hasParentModel($parent->key)) {
                    $model = $this->parentModels[$parent->key];
                }
            }
        }

        return $this->getMediaTransformer()
            ->transform($this->field, $model);
    }

    protected function hasParentModel($key)
    {
        return isset($this->parentModels[$key]) && $this->parentModels[$key] instanceof Model;
    }

    protected function asBoolean()
    {
        return (boolean) $this->getValue();
    }

    protected function getValue($key = null, $data = null)
    {
        return data_get(
            $data ?: $this->model,
            $key ?: $this->field->full_browse_key
        );
    }


}