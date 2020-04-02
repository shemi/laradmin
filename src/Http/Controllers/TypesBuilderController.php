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

        app('laradmin')->jsVars()
            ->set('routs.save', app('laradmin')->links()->route('laradmin.types.store'));

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

        app('laradmin')->jsVars()->set(
            'routs.save',
            app('laradmin')->links()->route('laradmin.types.update', [
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
        $panels = [];

        foreach (app('laradmin')->formPanels()->all() as $formPanel) {
            $panels[$formPanel->getCodeName()] = $formPanel->getBuilderData();
        }

        $fields = [];

        /** @var FormFormField $formField */
        foreach ($this->formFieldsManager->all() as $formField) {
            $fields[$formField->getCodename()] = $formField->getBuilderData();
        }

        $filters = [];

        foreach (app('laradmin')->filters()->all() as $filter) {
            $filters[] = [
                'key' => $filter->getName(),
                'label' => $filter->getLabel()
            ];
        }

        $actions = [];

        foreach (app('laradmin')->actions()->all() as $action) {
            $actions[] = [
                'key' => $action->getName(),
                'label' => $action->getLabel()
            ];
        }

        app('laradmin')->jsVars()->set([
            'model' => $model->toBuilder(),
            'builderData' => compact(
                'panels',
                'fields',
                'filters',
                'actions'
            )
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
            'panels' => 'required|array',
            'default_sort' => 'nullable',
            'records_per_page' => 'required|numeric',
            'default_sort_direction' => 'nullable|in:DESC,ASC',
            'filters' => 'array',
            'actions' => 'array',
            'soft_deletes' => 'nullable'
        ]);

        $errors = [];

        if($this->validateClassExistsAndExtends($data['controller'], BaseController::class)) {
            $errors['controller'] = ['The controller class most be subclass of "' . BaseController::class . '"'];
        }

        if($this->validateClassExistsAndExtends($data['model'], Model::class)) {
            $errors['model'] = ['The model class most be subclass of "' . Model::class . '"'];
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
        $type->icon = $data['icon'];
        $type->controller = $data['controller'];
        $type->panels = $data['panels'];
        $type->records_per_page = $data['records_per_page'];
        $type->default_sort = $data['default_sort'];
        $type->default_sort_direction = $data['default_sort_direction'];
        $type->filters = collect($data['filters'])->pluck('key')->all();
        $type->actions = collect($data['actions'])->pluck('key')->all();
        $type->soft_deletes = $data['soft_deletes'];

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
