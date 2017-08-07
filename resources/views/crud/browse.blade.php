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

            <b-table :data="data"
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
                    @foreach($columns as $column)
                        <b-table-column field="{{ $column->browse_key }}"
                                        label="{{ $column->browse_label }}"
                                        {{ $column->sortable ? 'sortable' : '' }}
                                        {{--{{ $column->is_numeric ? 'numeric' : '' }}--}}
                        >
                            <span v-text="props.row.{{ $column->browse_key }}"></span>
                        </b-table-column>
                    @endforeach
                </template>

            </b-table>

        </section>

    </crud-browse>

@endsection