<?php

namespace Shemi\Laradmin\Http\Controllers;

use Shemi\Laradmin\Data\DataNotFoundException;
use Shemi\Laradmin\Database\Schema\SchemaManager;
use Shemi\Laradmin\FormFields\FormField;
use Shemi\Laradmin\Models\Type;

class TypesBuilderController extends Controller
{

    public function index()
    {
        $types = Type::all();

        return view('laradmin::typeBuilder.browse', compact('types'));
    }

    public function query()
    {
        $types = Type::browseAll();

        return $this->response(compact('types'));
    }

    public function create()
    {
        $model = new Type;

        return $this->getCreateEditResponse($model);
    }

    public function edit($slug)
    {
        $model = Type::where('slug', $slug)->first();

        if(! $model) {
            throw new DataNotFoundException($slug);
        }

        return $this->getCreateEditResponse($model);
    }

    public function getCreateEditResponse(Type $model)
    {
        $panels = [
            'panel' => [
                'structure' => [
                    'id' => null,
                    'title' => 'New Panel',
                    'position' => null,
                    'is_main_meta' => false,
                    'fields' => (array) [],
                    'has_container' => true,
                    'style' => (object) []
                ],
                'options' => [
                    [
                        'label' => 'Title',
                        'type' => 'b-input',
                        'key' => 'title',
                        'props' => [
                            'type' => 'text',
                            'placeholder' => 'Enter Panel Name',
                        ],
                        'validation' => ['required']
                    ],
                    [
                        'label' => null,
                        'slot' => '<span>Has Container</span>',
                        'slot_el' => 'span',
                        'key' => 'has_container',
                        'type' => 'b-switch',
                        'validation' => []
                    ],
                    [
                        'label' => 'Fields',
                        'key' => 'fields',
                        'type' => 'la-fields-list',
                        'validation' => []
                    ]
                ]
            ]
        ];

        $fields = [];

        /** @var FormField $formField */
        foreach (app('laradmin')->formFields() as $formField) {
            $fields[$formField->getCodename()] = $formField->getBuilderData();
        }

        app('laradmin')->publishManyJs([
            'model' => $model->toBuilderArray(),
            'builderData' => compact('panels', 'fields')
        ]);

        return view('laradmin::typeBuilder.createEdit', compact('model'));
    }

}