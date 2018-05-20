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

    protected $formFieldsManager;

    public function __construct()
    {
        parent::__construct();

        $this->formFieldsManager = app('laradmin')->formFields();
    }

    public function index(Request $request)
    {
        if($this->user()->cant("browse types")) {
            return $this->responseUnauthorized($request);
        }

        $types = Type::all();

        return view('laradmin::typeBuilder.browse', compact('types'));
    }

    public function query(Request $request)
    {
        if($this->user()->cant("browse types")) {
            return $this->responseUnauthorized($request);
        }

        $types = Type::browseAll();

        return $this->response(compact('types'));
    }

    public function create(Request $request)
    {
        if($this->user()->cant("create types")) {
            return $this->responseUnauthorized($request);
        }

        $model = new Type;

        app('laradmin')->publishJs('routs.save', route('laradmin.types.store'));

        return $this->getCreateEditResponse($model);
    }

    public function store(Request $request)
    {
        if($this->user()->cant("create types")) {
            return $this->responseUnauthorized($request);
        }

        return $this->storeUpdateType($request, new Type);
    }

    public function edit($slug, Request $request)
    {
        if($this->user()->cant("update types")) {
            return $this->responseUnauthorized($request);
        }

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
        if($this->user()->cant("update types")) {
            return $this->responseUnauthorized($request);
        }

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
                    'position' => 'main',
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
        foreach ($this->formFieldsManager->all() as $formField) {
            $fields[$formField->getCodename()] = $formField->getBuilderData();
        }

        app('laradmin')->publishManyJs([
            'model' => $model->toBuilderArray(),
            'builderData' => compact('panels', 'fields')
        ]);

        return view('laradmin::typeBuilder.createEdit', compact('model'));
    }

    protected function validateClassExistsAndExtends($test, $base)
    {
        return ! class_exists($test) || ! is_subclass_of($test, $base);
    }

    protected function storeUpdateType(Request $request, Type $type)
    {
        $data = $this->validate($request, [
            'name' => 'required|string',
            'model' => 'required|string',
            'controller' => 'required|string',
            'icon' => 'string|nullable',
            'support_export' => 'nullable|boolean',
            'export_controller' => 'required_if:support_export,true',
            'support_import' => 'nullable|boolean',
            'import_controller' => 'required_if:support_import,true',
            'records_per_page' => 'required|numeric',
            'panels' => 'required|array',
            'default_sort' => 'nullable',
            'default_sort_direction' => 'nullable|in:DESC,ASC'
        ]);

        $errors = [];

        if($this->validateClassExistsAndExtends($data['controller'], BaseController::class)) {
            $errors['controller'] = ['The controller class most be subclass of "' . BaseController::class . '"'];
        }

        if($this->validateClassExistsAndExtends($data['model'], Model::class)) {
            $errors['model'] = ['The model class most be subclass of "' . Model::class . '"'];
        }

        if($data['support_export'] && $this->validateClassExistsAndExtends($data['export_controller'], BaseController::class)) {
            $errors['export_controller'] = ['The export controller class most be subclass of "' . BaseController::class . '"'];
        }

        if($data['support_import'] && $this->validateClassExistsAndExtends($data['import_controller'], BaseController::class)) {
            $errors['import_controller'] = ['The import controller class most be subclass of "' . BaseController::class . '"'];
        }

        foreach ($this->validatePanelsJsonSchema($data['panels']) as $path => $error) {
            $errors['panels'.$path] = $error;
        }

        if(! empty($errors)) {
            return $this->responseValidationError($errors);
        }

        $slug = str_slug($data['name']);

        if(! $type->exists) {
            $data['redirect'] = route('laradmin.types.edit', ['type' => $slug]);
        }

        $type->name = $data['name'];
        $type->slug = $slug;
        $type->model = $data['model'];
        $type->controller = $data['controller'];
        $type->panels = $data['panels'];
        $type->records_per_page = $data['records_per_page'];
        $type->support_export = (boolean) $data['support_export'];
        $type->export_controller = $data['export_controller'];
        $type->support_import = (boolean) $data['support_import'];
        $type->import_controller = $data['import_controller'];
        $type->default_sort = $data['default_sort'];
        $type->default_sort_direction = $data['default_sort_direction'];

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

        if(! app('laradmin')->formFields()->exists($data['type'])) {
            return ['.type' => ['the type: "'.$data['type'].'" not exists']];
        }

        /** @var FormFormField $formField */
        $formField = $this->formFieldsManager->get($data['type']);

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