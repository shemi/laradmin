@php
    if($menu->exist) {
        $pageTitle = trans('laradmin::menus.edit.page_title', ['name' => $menu->name]);
    } else {
        $pageTitle = trans('laradmin::menus.create.page_title');
    }
@endphp

@extends('laradmin::layouts.page', ['bodyClass' => 'menus-create-edit', 'pageTitle' => $pageTitle])

@section('content')

    <menu-builder :menu='{{ $menu->toJson() }}'
                  :routes='{{ $routes->toJson(JSON_UNESCAPED_UNICODE) }}'
                  inline-template>

        <div>

            <b-notification v-if="form.errors.has('form')" type="is-danger">
                @{{ form.errors.get("form") }}
            </b-notification>

            <b-field :type="form.errors.has('name') ? 'is-danger' : ''"
                     :message="form.errors.has('name') ? form.errors.get('name') : ''">
                <b-input placeholder="@lang('laradmin::menus.builder.name')"
                         type="text"
                         size="is-large"
                         v-model="form.name">
                </b-input>
            </b-field>

            <button class="button is-primary is-medium" @click="openNewEditModal()">Launch card modal</button>

            @include('laradmin::menus.blade.createEditModal')

            <icon-select-modal :active.sync="isIconSelectModalActive"
                               :selected.sync="itemForm.icon"></icon-select-modal>

        </div>

    </menu-builder>

@endsection