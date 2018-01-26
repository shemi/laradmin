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

            <b-table :data="data.data"
                     :loading="loading"

                     striped
                     checkable
                     mobile-cards
                     paginated
                     backend-pagination
                     :total="data.total"
                     :per-page="{{ $type->records_per_page }}"
                     backend-sorting
                     default-sort="id"
                     @sort="onSort"
                     @page-change="onPageChange"
                     :selected.sync="selected"
                     :checked-rows.sync="checkedRows">

                <template slot-scope="props">
                    @foreach($columns as $column)
                        <la-table-column field="{{ $column->key }}"
                                        label="{{ $column->browse_label }}"
                                        {{ $column->sortable ? 'sortable' : '' }}>

                            <div v-html="props.row.{{ $column->key }}"></div>

                        </la-table-column>
                    @endforeach

                    <la-table-column label="Actions" width="220">
                        <a :href="'{{ $editRoute }}'" class="button has-text-black">
                            Edit
                        </a>
                        <a :href="'{{ $editRoute }}'" class="button has-text-black">
                            View
                        </a>
                        <a class="button is-danger"
                           @click.prevent="onDelete('{{ $deleteRoute }}', '{{ str_singular($type->name) }}')">
                            Delete
                        </a>
                    </la-table-column>

                </template>

            </b-table>

        </section>

    </crud-browse>

@endsection