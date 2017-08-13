@extends('laradmin::layouts.page', [
    'bodyClass' => 'crud-browse',
    'pageTitle' => trans('laradmin::crud.page_title.browse', ['name' => $type->name]),
    'mainComponent' => 'crud'
])

@section('content')

    <crud-browse type-name="{{ $type->name }}"
                 type-slug="{{ $type->slug }}"
                 inline-template>

        <section class="section">

            <div class="level">
                <div class="level-left"></div>
                <div class="level-right">
                    <b-field>
                        <b-input placeholder="Search..."
                                 v-model="search"
                                 @input="onSearch"
                                 type="search" icon="search">
                        </b-input>
                    </b-field>
                </div>
            </div>

            <la-table :data="data"
                     bordered
                     striped
                     checkable
                     :loading="loading"
                     mobile-cards
                     paginated
                     :per-page="25"
                     default-sort="id"
                      @sort="onSort"
                      @page-change="onPageChange"
                     :selected.sync="selected"
                     :checked-rows.sync="checkedRows">

                <template scope="props">
                    @foreach($columns as $column)
                        <la-table-column field="{{ $column->key }}"
                                        label="{{ $column->label }}"
                                        {{ $column->sortable ? 'sortable' : '' }}
                                        {{--{{ $column->is_numeric ? 'numeric' : '' }}--}}
                        >
                            <span v-text="props.row.{{ $column->key }}"></span>
                        </la-table-column>
                    @endforeach

                    <la-table-column label="Actions" width="220">
                        <a :href="'{{ $editRoute }}'" class="button">
                            Edit
                        </a>
                        <a :href="'{{ $editRoute }}'" class="button">
                            View
                        </a>
                        <a class="button is-danger"
                           @click.prevent="onDelete('{{ $deleteRoute }}', '{{ str_singular($type->name) }}')">
                            Delete
                        </a>
                    </la-table-column>

                </template>

            </la-table>

        </section>

    </crud-browse>

@endsection