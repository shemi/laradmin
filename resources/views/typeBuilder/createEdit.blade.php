@php
    if($model->exist) {
        $pageTitle = trans('laradmin::type-builder.edit.page_title', ['name' => $model->name]);
    } else {
        $pageTitle = trans('laradmin::type-builder.create.page_title');
    }
@endphp

@extends('laradmin::layouts.page', ['bodyClass' => 'types-create-edit', 'pageTitle' => $pageTitle])

@include('laradmin::typeBuilder.blade.page-header')

@section('content')

    <type-create-edit inline-template>

        <div>

            <form v-on:submit.prevent="save()" novalidate>
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
                                            <b-field :type="form.errors.has('controller') ? 'is-danger' : ''"
                                                     :message="form.errors.has('controller') ? form.errors.get('controller') : ''"
                                                     label="@lang('laradmin::type-builder.builder.controller')">
                                                <b-input type="text" v-model="form.controller"></b-input>
                                            </b-field>
                                        </div>
                                        <div class="column">
                                            <b-field :type="form.errors.has('model') ? 'is-danger' : ''"
                                                     :message="form.errors.has('model') ? form.errors.get('model') : ''"
                                                     label="@lang('laradmin::type-builder.builder.model')">
                                                <b-input placeholder="e.g. \App\User"
                                                         type="text"
                                                         v-model="form.model">
                                                </b-input>
                                            </b-field>
                                        </div>
                                    </div>
                                    <div class="columns">
                                        <div class="column">
                                            <b-field :type="form.errors.has('records_per_page') ? 'is-danger' : ''"
                                                     :message="form.errors.has('records_per_page') ? form.errors.get('records_per_page') : ''"
                                                     label="@lang('laradmin::type-builder.builder.records_per_page')">
                                                <b-input type="number" :min="1"
                                                         v-model="form.records_per_page"></b-input>
                                            </b-field>
                                        </div>
                                        <div class="column">
                                            <b-field :type="form.errors.has('icon') ? 'is-danger' : ''"
                                                     label="@lang('laradmin::menus.builder.item_icon')"
                                                     :message="form.errors.has('icon') ? form.errors.get('icon') : ''">
                                                <b-field>
                                                    <p class="control icon-only-addon">
                                                        <b-icon :icon="form.icon"></b-icon>
                                                    </p>
                                                    <b-input type="text"
                                                             expanded
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

                                    <div class="columns">

                                        <div class="column">
                                            <b-field :type="form.errors.has('support_export') ? 'is-danger' : ''"
                                                     :message="form.errors.has('support_export') ? form.errors.get('support_export') : ''"
                                                     label="@lang('laradmin::type-builder.builder.support_export')">
                                                <b-switch v-model="form.support_export"></b-switch>
                                            </b-field>
                                        </div>

                                        <div class="column" v-show="form.support_export">
                                            <b-field :type="form.errors.has('export_controller') ? 'is-danger' : ''"
                                                     label="@lang('laradmin::type-builder.builder.export_controller')"
                                                     :message="form.errors.has('export_controller') ? form.errors.get('export_controller') : ''">
                                                <b-input type="text" v-model="form.export_controller">
                                            </b-field>
                                        </div>
                                    </div>

                                    <div class="columns">

                                        <div class="column">
                                            <b-field :type="form.errors.has('support_import') ? 'is-danger' : ''"
                                                     :message="form.errors.has('support_import') ? form.errors.get('support_import') : ''"
                                                     label="@lang('laradmin::type-builder.builder.support_import')">
                                                <b-switch v-model="form.support_import"></b-switch>
                                            </b-field>
                                        </div>

                                        <div class="column" v-show="form.support_import">
                                            <b-field :type="form.errors.has('import_controller') ? 'is-danger' : ''"
                                                     label="@lang('laradmin::type-builder.builder.import_controller')"
                                                     :message="form.errors.has('import_controller') ? form.errors.get('import_controller') : ''">
                                                <b-input type="text" v-model="form.import_controller">
                                            </b-field>
                                        </div>
                                    </div>

                                </div>

                            </div>
                        </section>

                        <section class="section la-browse-columns">

                            <div class="columns">
                                <div class="column browse-column-list">
                                    <div>
                                        <div class="level browse-column-header">
                                            <div class="level-left">
                                                <h3 class="level-item title is-5">
                                                    @lang('laradmin::type-builder.builder.columns')
                                                </h3>
                                            </div>
                                            <div class="level-right">
                                                <div class="level-item">
                                                    <b-dropdown position="is-bottom-left"
                                                                :disabled="! notBrowseColumns.length">
                                                        <button type="button"
                                                                slot="trigger"
                                                                class="button is-primary">
                                                            <span>@lang('laradmin::type-builder.builder.add_column')</span>
                                                            <b-icon icon="caret-down"></b-icon>
                                                        </button>

                                                        <b-dropdown-item v-for="column in notBrowseColumns"
                                                                         @click="addColumn(column)">
                                                            @{{ column.label }}
                                                        </b-dropdown-item>
                                                    </b-dropdown>
                                                </div>
                                            </div>
                                        </div>

                                        <la-columns-editor :fields="allFields" view="browse"></la-columns-editor>

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
                                                    <button type="button"
                                                            @click.prevent="addPanel('main')"
                                                            class="button is-primary">
                                                        @lang('laradmin::type-builder.builder.add_panel')
                                                    </button>
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

        </div>

    </type-create-edit>

@endsection