<?php

namespace Shemi\Laradmin\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;

class RelationshipController extends Controller
{

    public function query($typeSlug, $fieldKey, Request $request)
    {
        $type = $this->getTypeBySlug($typeSlug);
        /** @var Field $field */
        $field = $type->fields->first(function(Field $field) use ($fieldKey) {
            return $field->key === $fieldKey;
        });

        if(! $field) {
            return $this->responseNotFound();
        }

        $search = trim($request->input('search'));
        $typeModel = app($type->model);
        /** @var Model $relationModel */
        $relationModel = $field->getRelationModelClass($typeModel);
        $labels = (array) array_get($field->relationship, 'label', $relationModel->getKeyName());

        $query = $relationModel->select('*');

        if($search) {
            foreach ($labels as $index => $key) {
                if($index === 0) {
                    $query->where($key, 'like', "%{$search}%");
                } else {
                    $query->orWhere($key, 'like', "%{$search}%");
                }
            }
        }

        if($field->relation_image) {
            $query->with('media');
        }

        $results = $query->latest()
            ->paginate(15);

        $results->getCollection()
            ->transform(function ($model) use ($field) {
                return $field->transformRelationModel($model);
            });

        $results = $results->toArray();

        $results['has_image'] = (boolean) $field->relation_image;

        return $this->response($results);
    }

    public function simpleCreate($typeSlug, $fieldKey, Request $request)
    {
        $type = $this->getTypeBySlug($typeSlug);
        /** @var Field $field */
        $field = $type->fields->first(function(Field $field) use ($fieldKey) {
            return $field->key === $fieldKey;
        });

        if(! $field) {
            return $this->responseNotFound();
        }

        $this->validate($request, [
            $field->relation_labels[0] => 'required'
        ]);


        /** @var Type $relationType */
        $relationType = $field->relationship_type;
        /** @var Model $relationModel */
        $relationModel = app($relationType->model);

        $newItem = $this->insertCreateUpdateData($request, $relationModel, $relationType);

        return $this->response(
            $field->transformRelationModel($newItem)
        );
    }

}