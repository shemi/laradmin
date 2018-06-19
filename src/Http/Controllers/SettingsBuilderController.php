<?php

namespace Shemi\Laradmin\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Shemi\Laradmin\Exceptions\DataNotFoundException;
use Shemi\Laradmin\FormFields\FormFormField;
use Shemi\Laradmin\Models\Setting;
use Shemi\Laradmin\Models\SettingsPage;
use Shemi\Laradmin\Models\Type;
use Illuminate\Routing\Controller as BaseController;

class SettingsBuilderController extends Controller
{

    protected $formFieldsManager;

    public function __construct()
    {
        parent::__construct();

        $this->formFieldsManager = app('laradmin')->formFields();
    }

    public function index(Request $request)
    {
        if($this->user()->cant("browse settings")) {
            return $this->responseUnauthorized($request);
        }

        $pages = SettingsPage::all();

        return view('laradmin::settingsBuilder.browse', compact('pages'));
    }

    public function query(Request $request)
    {
        if($this->user()->cant("browse settings")) {
            return $this->responseUnauthorized($request);
        }

        $settings = SettingsPage::browseAll();

        return $this->response(compact('settings'));
    }

    public function create(Request $request)
    {
        if($this->user()->cant("create settings")) {
            return $this->responseUnauthorized($request);
        }

        $model = new SettingsPage;

        app('laradmin')->jsVars()
            ->set('routs.save', app('laradmin')->links()->route('laradmin.settings-builder.store'));

        return $this->getCreateEditResponse($model);
    }

    public function store(Request $request)
    {
        if($this->user()->cant("create settings")) {
            return $this->responseUnauthorized($request);
        }

        return $this->storeUpdatePage($request, new SettingsPage);
    }

    public function edit($slug, Request $request)
    {
        if($this->user()->cant("update settings")) {
            return $this->responseUnauthorized($request);
        }

        $model = SettingsPage::where('slug', $slug)->first();

        if(! $model) {
            throw new DataNotFoundException($slug);
        }

        app('laradmin')->jsVars()->set(
            'routs.save',
            app('laradmin')->links()->route('laradmin.settings-builder.update', [
                'type' => $slug
            ])
        );

        return $this->getCreateEditResponse($model);
    }

    public function update($slug, Request $request)
    {
        if($this->user()->cant("update settings")) {
            return $this->responseUnauthorized($request);
        }

        $page = SettingsPage::where('slug', $slug)->first();

        if(! $page) {
            throw new DataNotFoundException($slug);
        }

        return $this->storeUpdatePage($request, $page);
    }

    public function getCreateEditResponse(SettingsPage $model)
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

        app('laradmin')->jsVars()->set([
            'model' => $model->toBuilderArray(),
            'builderData' => compact('panels', 'fields')
        ]);

        return view('laradmin::settingsBuilder.createEdit', compact('model'));
    }

    protected function storeUpdatePage(Request $request, SettingsPage $page)
    {
        $data = $this->validate($request, [
            'name' => 'required|string',
            'bucket' => 'required|string',
            'icon' => 'string|nullable',
            'panels' => 'required|array'
        ]);

        $errors = [];

        foreach ($this->validatePanelsJsonSchema($data['panels']) as $path => $error) {
            $errors['panels'.$path] = $error;
        }

        if(! empty($errors)) {
            return $this->responseValidationError($errors);
        }

        $slug = str_slug($data['name']);

        if(! $page->exists) {
            $data['redirect'] = route('laradmin.settings-builder.edit', ['settings_builder' => $slug]);
        }

        $page->name = $data['name'];
        $page->slug = $slug;
        $page->panels = $data['panels'];
        $page->bucket = $data['bucket'];

        $data['saved'] = $page->save();

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