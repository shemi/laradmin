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
                <section class="section type-options">

                    @include('laradmin::typeBuilder.blade.actions-level')

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
                                    <b-field :type="form.errors.has('name') ? 'is-danger' : ''"
                                             :message="form.errors.has('name') ? form.errors.get('name') : ''"
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
                                    <b-field :type="form.errors.has('controller') ? 'is-danger' : ''"
                                             :message="form.errors.has('controller') ? form.errors.get('controller') : ''"
                                             label="@lang('laradmin::type-builder.builder.records_per_page')">
                                        <b-input type="number" :min="1" v-model="form.records_per_page"></b-input>
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

                        {{--<div class="column panels-area">--}}
                            {{--<div>--}}
                                {{--<div class="level panels-area-header">--}}
                                    {{--<div class="level-left">--}}
                                        {{--<h3 class="level-item title is-5">--}}
                                            {{--@lang('laradmin::type-builder.builder.side_panels')--}}
                                        {{--</h3>--}}
                                    {{--</div>--}}
                                    {{--<div class="level-right">--}}
                                        {{--<div class="level-item">--}}
                                            {{--<button type="button"--}}
                                                    {{--@click.prevent="addPanel('side')"--}}
                                                    {{--class="button is-primary">--}}
                                                {{--@lang('laradmin::type-builder.builder.add_panel')--}}
                                            {{--</button>--}}
                                        {{--</div>--}}
                                    {{--</div>--}}
                                {{--</div>--}}

                                {{--<la-panel-list class="side-panels"--}}
                                               {{--form-key="side_panels"--}}
                                               {{--ref="side_panels"--}}
                                               {{--:panels="form.side_panels">--}}
                                {{--</la-panel-list>--}}
                            {{--</div>--}}
                        {{--</div>--}}
                    </div>

                </section>
            </form>

            <icon-select-modal :active.sync="isIconSelectModalActive"
                               :selected-icon.sync="form.icon">
            </icon-select-modal>

        </div>

    </type-create-edit>

@endsection