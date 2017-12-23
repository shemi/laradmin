<?php

namespace Shemi\Laradmin\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Shemi\Laradmin\Data\DataNotFoundException;
use Shemi\Laradmin\Database\Schema\SchemaManager;
use Shemi\Laradmin\FormFields\FormField;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Panel;
use Shemi\Laradmin\Models\Type;
use Illuminate\Routing\Controller as BaseController;

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

        app('laradmin')->publishJs('routs.save', route('laradmin.types.store'));

        return $this->getCreateEditResponse($model);
    }

    public function store(Request $request)
    {
        return $this->storeUpdateType($request, new Type);
    }

    public function edit($slug)
    {
        $model = Type::where('slug', $slug)->first();

        if(! $model) {
            throw new DataNotFoundException($slug);
        }

        app('laradmin')->publishJs(
            'routs.save',
            route('laradmin.types.update', [
                'type' => $slug
            ])
        );

        return $this->getCreateEditResponse($model);
    }

    public function update($slug, Request $request)
    {
        $type = Type::where('slug', $slug)->first();

        if(! $type) {
            throw new DataNotFoundException($slug);
        }

        return $this->storeUpdateType($request, $type);
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

    protected function storeUpdateType(Request $request, Type $type)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'model' => 'required|string',
            'controller' => 'required|string',
            'icon' => 'string|nullable',
            'records_per_page' => 'required|numeric',
            'panels' => 'required|array'
        ]);

        $errors = [];
        $controller = $request->input('controller');

        if(! class_exists($controller) || ! is_subclass_of($controller, BaseController::class)) {
            $errors['controller'] = ['The controller class most be subclass of "' . BaseController::class . '"'];
        }

        $model = $request->input('model');

        if(! class_exists($model) || ! is_subclass_of($model, Model::class)) {
            $errors['model'] = ['The model class most be subclass of "' . Model::class . '"'];
        }

        $panels = $request->input('panels');



        if(! empty($errors)) {
            return $this->responseValidationError($errors);
        }

        return $this->response($request->all());
    }

    protected function validatePanelsJsonSchema($data)
    {

    }

    protected function validatePanelJsonSchema($data)
    {

    }

    protected function validateFormFieldJsonSchema($data, Field $field)
    {

    }

}