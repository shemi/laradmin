<?php

namespace Shemi\Laradmin\Http\Controllers;

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

        $schemas = [
            'panel' => [
                'schema' => [
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
                        'label' => 'Has Container',
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

        /** @var FormField $formField */
        foreach (app('laradmin')->formFields() as $formField) {
            $schemas[$formField->getCodename()] = [
                'schema' => $formField->getBuilderSchema(),
                'options' => $formField->getBuilderOptions()
            ];
        }

        app('laradmin')->publishManyJs([
            'model' => $model->toBuilderArray(),
            'schemas' => $schemas
        ]);

        return view('laradmin::typeBuilder.createEdit', compact('model'));
    }

}