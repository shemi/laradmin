@php
    if($type->exist) {
        $pageTitle = trans('laradmin::type-builder.edit.page_title', ['name' => $type->name]);
    } else {
        $pageTitle = trans('laradmin::type-builder.create.page_title');
    }
@endphp

@extends('laradmin::layouts.page', ['bodyClass' => 'types-create-edit', 'pageTitle' => $pageTitle])

@section('content')

    <type-create-edit :type='{{ $type->toJson() }}'
                      :tables='{{ json_encode($tables, JSON_UNESCAPED_UNICODE) }}'
                      inline-template>

        <div>

            <form v-on:submit.prevent="save()" novalidate>

                <div class="columns">

                    <div class="column is-three-quarters">

                        <section class="section">
                            @include('laradmin::components.forms.globalFormErrors', ['key' => 'form'])

                            <div class="columns">
                                <div class="column">
                                    <b-field :type="form.errors.has('name') ? 'is-danger' : ''"
                                             :message="form.errors.has('name') ? form.errors.get('name') : ''">
                                        <b-input placeholder="@lang('laradmin::type-builder.builder.group_name')"
                                                 type="text"
                                                 size="is-large"
                                                 v-model="form.name">
                                        </b-input>
                                    </b-field>
                                </div>
                            </div>
                            <div class="columns">
                                <div class="column">
                                    <b-field :type="form.errors.has('table') ? 'is-danger' : ''"
                                             :message="form.errors.has('table') ? form.errors.get('table') : ''"
                                             label="Table">
                                        <b-select v-model="form.table" placeholder="Select a table" expanded>
                                            <option v-for="(object, table) in tables"
                                                    :value="object"
                                                    :key="table">
                                                @{{ table }}
                                            </option>
                                        </b-select>
                                    </b-field>

                                </div>
                                <div class="column">
                                    <b-field :type="form.errors.has('name') ? 'is-danger' : ''"
                                             :message="form.errors.has('name') ? form.errors.get('name') : ''">
                                        <b-input placeholder="@lang('laradmin::type-builder.builder.group_name')"
                                                 type="text"
                                                 v-model="form.name">
                                        </b-input>
                                    </b-field>
                                </div>
                            </div>
                        </section>

                        <section class="section fields-group">

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
                                    @if($type->exists)
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

                        </section>

                    </div>

                </div>

            </form>

            {{--<icon-select-modal :active.sync="isIconSelectModalActive"--}}
                               {{--:selected-icon.sync="itemForm.icon">--}}
            {{--</icon-select-modal>--}}

        </div>

    </type-create-edit>

@endsection