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

        <section class="section">

            <div class="level">
                <div class="level-left">
                    <b-field>
                        @foreach($type->filterable_fields as $field)
                                <b-select placeholder="Filter by {{ $field->browse_label }}"
                                          v-model="query.filters['{{ $field->key }}']"
                                          @focus="fetchFilterData('{{ $field->key }}')"
                                          @input="onFilter"
                                          :loading="filtersData['{{ $field->key }}']['loading']">

                                    <option :value="null" disabled v-if="filtersData['{{ $field->key }}']['loading']">
                                        Loading...
                                    </option>

                                    <option :value="null" v-if="filtersData['{{ $field->key }}']['loaded']">
                                        Filter by {{ $field->browse_label }}
                                    </option>

                                    <option v-for="(label, key) in filtersData['{{ $field->key }}']['data']"
                                            :key="key"
                                            :value="key">
                                        @{{ label }}
                                    </option>

                                </b-select>
                        @endforeach
                    </b-field>
                </div>

                <div class="level-right">
                    @if($type->searchable_fields->isNotEmpty())
                        <b-field>
                            <b-input placeholder="Search..."
                                     v-model="search"
                                     @input="onSearch"
                                     type="search" icon="search">
                            </b-input>
                        </b-field>
                    @endif
                </div>
            </div>

            <div class="level">
                <div class="level-left">
                    @if(Laradmin::user()->can('delete ' . $type->slug))
                        <div class="field has-addons">
                            <p class="control">
                                <a class="button is-danger"
                                   :disabled="! checkedRows.length"
                                   @click.prevent="onDeleteSelected(checkedRows, '{{ $deleteManyRoute }}', '{{ str_plural($type->name) }}', '{{ $primaryKey }}')">
                                    <b-icon icon="trash"></b-icon>
                                    <span>
                                        Delete Selected @{{ checkedRows.length ? '('+ checkedRows.length +')' : '' }}
                                    </span>
                                </a>
                            </p>
                        </div>
                    @endif
                </div>
                <div class="level-right">

                    <div class="field has-addons">
                        @if($type->support_export && Laradmin::user()->can('export ' . $type->slug))
                            <p class="control">
                                <a class="button" @click.prevent="onExport">
                                    <b-icon icon="download"></b-icon>
                                    <span>Export @{{ checkedRows.length ? '('+ checkedRows.length +')' : 'All' }}</span>
                                </a>
                            </p>
                        @endif
                        @if($type->support_import && Laradmin::user()->can('import ' . $type->slug))
                            <p class="control">
                                <a class="button" @click.prevent="onImport">
                                    <b-icon icon="upload"></b-icon>
                                    <span>Import</span>
                                </a>
                            </p>
                        @endif
                    </div>

                </div>
            </div>

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
                     @page-change="onPageChange"
                     :checked-rows.sync="checkedRows">

                <template slot-scope="props">
                    @foreach($columns as $index => $column)
                        <b-table-column field="{{ $column->browse_key }}"
                                        label="{{ $column->browse_label }}"
                                        custom-key="{{ $column->browse_key.$index }}"
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

                    <b-table-column label="" class="la-actions-cell">
                        @if(Laradmin::user()->can('view ' . $type->slug))
                            <a :href="'{{ $editRoute }}'">
                                <b-icon icon="eye"></b-icon>
                            </a>
                        @endif
                        @if(Laradmin::user()->can('update ' . $type->slug))
                            <a :href="'{{ $editRoute }}'">
                                <b-icon icon="pencil-square-o"></b-icon>
                            </a>
                        @endif
                        @if(Laradmin::user()->can('delete ' . $type->slug))
                            <a @click.prevent="onDelete('{{ $deleteRoute }}', '{{ str_singular($type->name) }}')">
                                <b-icon icon="trash"></b-icon>
                            </a>
                        @endif
                    </b-table-column>

                </template>

            </b-table>

        </section>

    </crud-browse>

@endsection