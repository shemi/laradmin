<?php

namespace Shemi\Laradmin\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Shemi\Laradmin\Exceptions\DataNotFoundException;
use Shemi\Laradmin\FormFields\FormFormField;
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

        /** @var FormFormField $formField */
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
        $data = $this->validate($request, [
            'name' => 'required|string',
            'model' => 'required|string',
            'controller' => 'required|string',
            'icon' => 'string|nullable',
            'records_per_page' => 'required|numeric',
            'panels' => 'required|array'
        ]);

        $errors = [];

        if(! class_exists($data['controller']) || ! is_subclass_of($data['controller'], BaseController::class)) {
            $errors['controller'] = ['The controller class most be subclass of "' . BaseController::class . '"'];
        }

        if(! class_exists($data['model']) || ! is_subclass_of($data['model'], Model::class)) {
            $errors['model'] = ['The model class most be subclass of "' . Model::class . '"'];
        }

        foreach ($this->validatePanelsJsonSchema($data['panels']) as $path => $error) {
            $errors['panels'.$path] = $error;
        }

        if(! empty($errors)) {
            return $this->responseValidationError($errors);
        }

        $type->name = $data['name'];
        $type->slug = str_slug($data['name']);
        $type->model = $data['model'];
        $type->panels = $data['panels'];
        $type->records_per_page = $data['records_per_page'];

        $data['saved'] = $type->save();

        return $this->response($data);
    }

    protected function validatePanelsJsonSchema($panels)
    {
        $errors = [];

        foreach ($panels as $index => $panel) {
            $panelErrors = [];

            foreach ($this->validateFormFieldsJsonSchema($panel['fields']) as $path => $error) {
                $panelErrors['.fields'.$path] = $error;
            }

            foreach ($panelErrors as $path => $error) {
                $errors['.'.$index.$path] = $error;
            }
        }

        return $errors;
    }

    protected function validateFormFieldsJsonSchema($fields)
    {
        $errors = [];

        foreach ($fields as $index => $field) {
            foreach ($this->validateFormFieldJsonSchema($field) as $path => $error) {
                $errors['.'.$index.$path] = $error;
            }
        }

        return $errors;
    }

    protected function validateFormFieldJsonSchema($data)
    {
        if(! isset($data['type'])) {
            return ['.type' => ['the type property is required']];
        }

        if(! app('laradmin')->formFieldExists($data['type'])) {
            return ['.type' => ['the type: "'.$data['type'].'" not exists']];
        }

        /** @var FormFormField $formField */
        $formField = app('laradmin')->formField($data['type']);

        $validator = $formField->schema()->validate($data, '.');

        $errors = $validator->errors();

        if($formField->isSupportingSubFields() && ! empty($data['fields'])) {
            foreach ($this->validateFormFieldsJsonSchema($data['fields']) as $path => $error) {
                $errors['.fields'.$path] = $error;
            }
        }

        return $errors;
    }

}