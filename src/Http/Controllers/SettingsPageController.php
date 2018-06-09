<?php

namespace Shemi\Laradmin\Http\Controllers;

use Illuminate\Http\Request;
use Laradmin;
use Shemi\Laradmin\Contracts\Repositories\SettingsRequestValidatorRepository;
use Shemi\Laradmin\Contracts\Repositories\TransformSettingsDataRepository;
use Shemi\Laradmin\Exceptions\DataNotFoundException;
use Shemi\Laradmin\Models\SettingsPage;
use Shemi\Laradmin\Repositories\SetSettingsRepository;

class SettingsPageController extends Controller
{

    public function editResponse($pageSlug, Request $request)
    {
        $user = $this->user();
        $page = SettingsPage::whereSlug($pageSlug);

        if(! $page) {
            return $this->responseNotFound("Cant find page with the slug {$pageSlug}");
        }

        $view = 'laradmin::settings.edit';

        if(view()->exists("laradmin::settings.{$page->slug}.edit")) {
            $view = "laradmin::settings.{$page->slug}.edit";
        }

        $data = $page->getRelationData();

        $form = app(TransformSettingsDataRepository::class)
            ->transform($page);

        $viewType = "any";

        app('laradmin')->jsVars()
            ->set([
                'model' => $form,
                'relation_data' => $data,
                'routs.save' => route("laradmin.settings.update", ['pageSlug' => $page->slug]),
                'routs.upload' => route("laradmin.upload", ['type' => "settings__".$page->slug]),
                'type' => [
                    'name' => $page->name,
                    'slug' => $page->slug,
                    'id' => $page->id,
                    'types' => SettingsPage::getAllFieldTypes($page->fields)
                ]
            ]);

        return view($view, compact('page', 'data', 'viewType', 'user'));
    }

    /**
     * @param SettingsPage $page
     * @param Request $request
     * @return boolean
     */
    protected function userCanUpdate(SettingsPage $page, Request $request)
    {
        return true; //$this->user()->can("update {$type->slug}");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  string $pageSlug
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function edit($pageSlug, Request $request)
    {
        $page = SettingsPage::whereSlug($pageSlug);

        if(! $page) {
            throw new DataNotFoundException($pageSlug);
        }

        if(! $this->userCanUpdate($page, $request)) {
            return $this->responseUnauthorized($request);
        }

        return $this->editResponse($pageSlug, $request);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $pageSlug
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $pageSlug)
    {
        $page = SettingsPage::whereSlug($pageSlug);

        if(! $page) {
            throw new DataNotFoundException($pageSlug);
        }

        if(! $this->userCanUpdate($page, $request)) {
            return $this->responseUnauthorized($request);
        }

        app(SettingsRequestValidatorRepository::class)
            ->validate($request, $page);

        (new SetSettingsRepository())->set($request->all(), $page);

        $model = app(TransformSettingsDataRepository::class)
            ->transform($page);

        $redirect = false;

        return $this->response(
            compact('model', 'redirect')
        );
    }

}
