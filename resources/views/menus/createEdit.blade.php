@php
    if($menu->exists) {
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
                                            <p class="title is-3 is-spaced">
                                                @lang('laradmin::menus.builder.title')
                                            </p>
                                            <p class="subtitle">
                                                @lang('laradmin::menus.builder.subtitle')
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="level-right">
                                    <div class="level-item">
                                        <button class="button is-primary is-medium"
                                                type="button"
                                                @click="openNewEditModal()">
                                            <b-icon icon="add"></b-icon>
                                            <span>@lang('laradmin::menus.builder.new_item_button')</span>
                                        </button>
                                    </div>
                                </div>

                            </div>

                            <hr>

                            <draggable v-model="form.items" :options="{group:'menu'}" class="menu-items">
                                <menu-builder-item v-for="(item, index) in form.items"
                                                   :key="item.id"
                                                   :position="index"
                                                   v-on:edit="openNewEditModal($event.item, $event.position)"
                                                   v-on:delete="deleteMenuItem($event)"
                                                   :item="item">
                                </menu-builder-item>
                            </draggable>

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
                                    @if($menu->exists)
                                        <a class="button is-link is-small is-danger is-outlined">
                                            @lang('laradmin::template.delete')
                                        </a>
                                    @endif
                                @endslot

                                @component('laradmin::components.meta-line', ['langKey' => 'laradmin::template.created_at'])
                                    form.created_at
                                @endcomponent

                                @component('laradmin::components.meta-line', ['langKey' => 'laradmin::template.updated_at'])
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

                            <b-message title="@lang('laradmin::template.how_to_use')" type="is-info">
                                @lang('laradmin::menus.how_to_use_description')
                                <code>menu('@{{ form.name || 'menu name' | slugify }}')</code>
                            </b-message>

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