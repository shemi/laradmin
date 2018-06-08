@php
    if($model->exists) {
        $pageTitle = trans('laradmin::settings_builder.edit.page_title', ['name' => $model->name]);
    } else {
        $pageTitle = trans('laradmin::settings_builder.create.page_title');
    }
@endphp

@extends('laradmin::layouts.page', ['bodyClass' => 'settings-create-edit', 'pageTitle' => $pageTitle])

@include('laradmin::settingsBuilder.blade.page-header')

@section('content')

    <settings-builder-create-edit inline-template>

        <div>

            <form v-on:submit.prevent="save" action="/" method="POST" novalidate>
                <div class="columns">

                    <div class="column is-three-quarters">
                        <section class="section type-options">

                            <div class="columns">
                                <div class="column">

                                    @include('laradmin::components.forms.globalFormErrors', ['key' => 'form'])

                                    <div class="columns">
                                        <div class="column">
                                            <b-field :type="form.errors.has('name') ? 'is-danger' : ''"
                                                     :message="form.errors.has('name') ? form.errors.get('name') : ''">
                                                <b-input placeholder="@lang('laradmin::type-builder.builder.name')"
                                                         type="text"
                                                         size="is-large"
                                                         v-model="form.name">
                                                </b-input>
                                            </b-field>
                                        </div>
                                    </div>

                                    <div class="columns">
                                        <div class="column">
                                            <b-field :type="form.errors.has('icon') ? 'is-danger' : ''"
                                                     label="@lang('laradmin::menus.builder.item_icon')"
                                                     :message="form.errors.has('icon') ? form.errors.get('icon') : ''">
                                                <b-field>
                                                    <b-input type="text"
                                                             expanded
                                                             :icon="form.icon"
                                                             v-model="form.icon">
                                                    </b-input>
                                                    <p class="control">
                                                        <button type="button"
                                                                @click="openIconSelectModal"
                                                                class="button is-primary">
                                                            <b-icon icon="smile-o"></b-icon>
                                                        </button>
                                                    </p>
                                                </b-field>
                                            </b-field>
                                        </div>
                                    </div>

                            </div>
                        </section>

                        <section class="section panels">

                            <div class="columns">
                                <div class="column panels-area">
                                    <div>
                                        <div class="level panels-area-header">
                                            <div class="level-left">
                                                <h3 class="level-item title is-5">
                                                    @lang('laradmin::type-builder.builder.panels')
                                                </h3>
                                            </div>
                                            <div class="level-right">
                                                <div class="level-item">
                                                    <b-dropdown>
                                                        <button type="button" class="button is-primary" slot="trigger">
                                                            <span>@lang('laradmin::type-builder.builder.add_panel')</span>
                                                            <b-icon icon="caret-down"></b-icon>
                                                        </button>

                                                        <b-dropdown-item @click="addPanel(type)"
                                                                         v-if="! panel.protected"
                                                                         v-for="(panel, type) in panels">
                                                            @{{ panel.name }}
                                                        </b-dropdown-item>
                                                    </b-dropdown>
                                                </div>
                                            </div>
                                        </div>

                                        <la-panel-list class="main-panels"
                                                       form-key="panels"
                                                       ref="panels"
                                                       :panels="form.panels">
                                        </la-panel-list>
                                    </div>
                                </div>

                            </div>

                        </section>
                    </div>

                    <div class="column">
                        <section class="section">
                            @component('laradmin::components.meta-box')

                                @slot('title')
                                    @lang('laradmin::template.publish')
                                @endslot

                                @slot('footer')
                                    <button type="submit"
                                            :class="{'is-loading': form.busy}"
                                            class="button is-primary">
                                        @lang('laradmin::template.save')
                                    </button>
                                    @if($model->exists)
                                        <a class="button is-link is-small is-danger is-outlined">
                                            @lang('laradmin::template.delete')
                                        </a>
                                    @endif
                                @endslot

                                @component('laradmin::components.meta-line', ['langKey' => 'laradmin::template.created_at', 'filter' => 'date'])
                                    form.created_at
                                @endcomponent

                                @component('laradmin::components.meta-line', ['langKey' => 'laradmin::template.updated_at', 'filter' => 'date'])
                                    form.updated_at
                                @endcomponent

                                @component('laradmin::components.meta-line', [
                                    'langKey' => 'laradmin::template.slug',
                                    'filter' => 'slugify',
                                    'hr' => false
                                ])
                                    form.name
                                @endcomponent

                            @endcomponent
                        </section>
                    </div>

                </div>
            </form>

            <icon-select-modal :active.sync="isIconSelectModalActive"
                               :selected-icon.sync="form.icon">
            </icon-select-modal>

            <div class="la-page-loading" :class="{'is-active': isLoading}">
                <div class="la-logo">
                    @include('laradmin::layouts.blade.logo')
                </div>
                <div class="lds-ring"><div></div><div></div><div></div><div></div></div>
            </div>

        </div>

    </settings-builder-create-edit>

@endsection