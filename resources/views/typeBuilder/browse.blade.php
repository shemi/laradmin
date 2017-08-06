@extends('laradmin::layouts.page', [
    'bodyClass' => 'type-builder-browse',
    'pageTitle' => trans('laradmin::type-builder.browse.page_title')
])

@php
    $editRoute = route('laradmin.menus.menus.edit', ['menu' => "__menu__"]);
    $editRoute = str_replace('__menu__', '\'+ props.row.slug +\'', $editRoute);
@endphp

@section('content')

    <browse-types :types='{{ json_encode($types, JSON_UNESCAPED_UNICODE) }}' inline-template>

        <section class="section">

            <b-table :data="types"
                     bordered
                     striped
                     checkable
                     :loading="loading"
                     mobile-cards
                     paginated
                     :per-page="25"
                     default-sort="id"
                     :selected.sync="selected"
                     :checked-rows.sync="checkedRows">

                <template scope="props">
                    <b-table-column field="id" label="ID" width="40" sortable numeric>
                        @{{ props.row.id }}
                    </b-table-column>

                    <b-table-column field="name" label="Name" sortable>
                        @{{ props.row.name }}
                    </b-table-column>

                    <b-table-column field="location" label="Location" sortable>
                        @{{ props.row.location }}
                    </b-table-column>

                    <b-table-column field="updated_at" label="Updated" sortable>
                        <span v-html="formatDate(props.row.updated_at)"></span>
                    </b-table-column>

                    <b-table-column field="created_at" label="Created" sortable>
                        <span v-html="formatDate(props.row.created_at)"></span>
                    </b-table-column>

                    <b-table-column field="actions" label="Actions">
                        <a :href="'{{ $editRoute }}'" class="button">
                            Edit
                        </a>
                    </b-table-column>
                </template>

            </b-table>
        </section>


    </browse-types>

@endsection