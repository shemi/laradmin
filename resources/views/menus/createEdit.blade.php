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

            <form v-on:submit.prevent="save()" novalidate>

                <div class="columns">

                    <div class="column is-three-quarters">

                        <section class="section">
                            @include('laradmin::components.forms.globalFormErrors', ['key' => 'form'])

                            <b-field :type="form.errors.has('name') ? 'is-danger' : ''"
                                     :message="form.errors.has('name') ? form.errors.get('name') : ''">
                                <b-input placeholder="@lang('laradmin::menus.builder.name')"
                                         type="text"
                                         size="is-large"
                                         v-model="form.name">
                                </b-input>
                            </b-field>
                        </section>

                        <section class="section menu-structure">
                            <div class="level section-header">

                                <div class="level-left">
                                    <div class="level-item">
                                        <div>
                                            <p class="title is-3 is-spaced">Menu Structure</p>
                                            <p class="subtitle">Drag each item into the order you prefer.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="level-right">
                                    <div class="level-item">
                                        <button class="button is-primary is-medium" @click="openNewEditModal()">
                                            <b-icon icon="add"></b-icon>
                                            <span>New Item</span>
                                        </button>
                                    </div>
                                </div>

                            </div>

                            <hr>

                            <div class="menu-items" v-dragula="items" drake="menus" service="menus">
                                <menu-builder-item v-for="(item, index) in items"
                                                   :key="item.id"
                                                   :position="index"
                                                   v-on:edit="openNewEditModal($event.item, $event.position)"
                                                   v-on:delete="deleteMenuItem($event)"
                                                   :item="item">
                                </menu-builder-item>
                            </div>

                        </section>

                    </div>

                    <div class="column">

                        <section class="section">

                            <div class="card meta-box">
                                <header class="card-header">
                                    <p class="card-header-title">Publish</p>
                                </header>
                                <div class="card-content">
                                    <div class="content">

                                        <div>Created At: <b>Not available</b></div>
                                        <hr>
                                        <div>Updated At: <b>Not available</b></div>

                                    </div>
                                </div>
                                <footer class="card-footer">
                                    <a class="button is-primary">Save</a>
                                    <a class="button is-link is-small is-danger is-outlined">Delete</a>
                                </footer>
                            </div>

                        </section>

                    </div>

                </div>

            </form>

            @include('laradmin::menus.blade.createEditModal')

            <icon-select-modal :active.sync="isIconSelectModalActive"
                               :selected-icon.sync="itemForm.icon">
            </icon-select-modal>

        </div>

    </menu-builder>

@endsection