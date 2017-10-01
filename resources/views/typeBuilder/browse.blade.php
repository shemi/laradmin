@extends('laradmin::layouts.page', [
    'bodyClass' => 'type-builder-browse',
    'pageTitle' => trans('laradmin::type-builder.browse.page_title')
])

@php
    $editRoute = route('laradmin.types.edit', ['menu' => "__type__"]);
    $editRoute = str_replace('__type__', '\'+ props.row.slug +\'', $editRoute);
@endphp

@include('laradmin::typeBuilder.blade.page-header')

@section('content')

    <browse-types inline-template>

        <section class="section">

            <div class="level">
                <div class="level-left"></div>
                <div class="level-right">
                    <b-field>
                        <b-input placeholder="Search..."
                                 v-model="search"
                                 type="search"
                                 icon="search">
                        </b-input>
                    </b-field>
                </div>
            </div>

            <b-table :data="filteredTypes"
                     bordered
                     striped
                     :checkable="false"
                     :loading="loading"
                     mobile-cards
                     paginated
                     :per-page="25"
                     :default-sort="['id', 'asc']"
                     default-sort-direction="asc"
                     ref="table"
                     :selected.sync="selected">

                <template scope="props">
                    <b-table-column field="id" label="ID" width="40" sortable>
                        @{{ props.row.id }}
                    </b-table-column>

                    <b-table-column field="name" label="Name" sortable>
                        @{{ props.row.name }}
                    </b-table-column>

                    <b-table-column field="slug" label="Slug" sortable>
                        @{{ props.row.slug }}
                    </b-table-column>

                    <b-table-column field="updated_at" label="Updated" sortable>
                        <span>@{{ props.row.updated_at | date }}</span>
                    </b-table-column>

                    <b-table-column field="created_at" label="Created" sortable>
                        <span>@{{ props.row.created_at | date }}</span>
                    </b-table-column>

                    <b-table-column field="panels_count" label="Panels" numeric>
                        @{{ props.row.panels_count }}
                    </b-table-column>

                    <b-table-column field="fields_count" label="Fields" numeric>
                        @{{ props.row.fields_count }}
                    </b-table-column>

                    <b-table-column field="actions" label="Actions" numeric>
                        <a :href="'{{ $editRoute }}'" class="button">
                            Edit
                        </a>
                    </b-table-column>
                </template>

            </b-table>
        </section>


    </browse-types>

@endsection