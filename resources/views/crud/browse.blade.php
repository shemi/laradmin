@extends('laradmin::layouts.page', [
    'bodyClass' => 'crud-browse',
    'pageTitle' => trans('laradmin::crud.page_title.browse', ['name' => $type->name]),
    'mainComponent' => 'crud'
])

@section('content')

    <crud-browse type-name="{{ $type->name }}"
                 type-slug="{{ $type->slug }}"
                 :filterable-fields="{{ json_encode($type->filterable_fields->pluck('key'), JSON_UNESCAPED_UNICODE) }}"
                 inline-template>

        <section class="page-section">

            <div class="la-crud-table-container">

                @include('laradmin::crud.blade.browse-table-controls')

                <b-table :data="data.data"
                         :loading="loading"
                         striped
                         checkable
                         mobile-cards
                         hoverable
                         paginated
                         backend-pagination
                         :total="data.total"
                         :per-page="{{ $type->records_per_page }}"
                         backend-sorting
                         :default-sort="query.order_by || '{{ $type->default_sort }}'"
                         :default-sort-direction="query.order || '{{ $type->default_sort_direction }}'"
                         @sort="onSort"
                         :current-page="query.page"
                         @check="onTableCheckChanged"
                         @page-change="onPageChange"
                         :checked-rows.sync="checkedRows">

                    <template slot-scope="props">
                        @foreach($columns as $index => $column)
                            <b-table-column field="{{ $column->browse_key }}"
                                            label="{{ $column->browse_label }}"
                                            custom-key="{{ $column->browse_key.$index }}"
                                    {{ $column->type === 'image' ? 'centered ' : '' }}
                                    {{ $column->sortable ? 'sortable' : '' }}>

                                <la-field-renderer :value="props.row.{{ $column->browse_key }}"
                                                   type="{{ $column->type }}"
                                                   empty-string=""
                                                   :template-options="{{ attr_json_encode($column->template_options) }}"
                                                   :browse-settings="{{ attr_json_encode($column->browse_settings) }}"
                                                   form-key="{{ $column->browse_key }}">
                                </la-field-renderer>

                            </b-table-column>
                        @endforeach

                        <b-table-column label="" class="la-actions-cell" width="148" numeric>
                            <p class="buttons">
                                <template v-if="! isTrash">
                                    @if(Laradmin::user()->can('view ' . $type->slug))
                                        <a class="button" :href="'{{ $editRoute }}'">
                                            <b-icon icon="eye"></b-icon>
                                        </a>
                                    @endif
                                    @if(Laradmin::user()->can('update ' . $type->slug))
                                        <a class="button" :href="'{{ $editRoute }}'">
                                            <b-icon icon="pencil-square-o"></b-icon>
                                        </a>
                                    @endif
                                </template>
                                @if(Laradmin::user()->can('delete ' . $type->slug))
                                    <a class="button is-danger" @click.prevent="onDelete('{{ $deleteRoute }}', '{{ str_singular($type->name) }}')">
                                        <b-icon icon="trash"></b-icon>
                                    </a>
                                @endif
                                <template v-if="isTrash">
                                    @if(Laradmin::user()->can('restore ' . $type->slug))
                                        <a class="button is-info" @click.prevent="onRestore('{{ $restoreRoute }}', '{{ str_singular($type->name) }}')">
                                            <b-icon icon="undo"></b-icon>
                                        </a>
                                    @endif
                                </template>
                            </p>
                        </b-table-column>

                    </template>

                </b-table>
            </div>

        </section>

    </crud-browse>

@endsection
