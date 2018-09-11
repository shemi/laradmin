<div class="la-crud-table-controls">

    <div class="tabs">
        <ul>
            <li class="is-active">
                <a>{{ trans('Index') }}</a>
            </li>
            <li>
                <a>{{ trans('Trashed') }}</a>
            </li>
        </ul>
    </div>

    <div class="level">
        <div class="level-left">
            @if($type->searchable_fields->isNotEmpty())
                <b-field>
                    <b-input placeholder="Search..."
                             rounded
                             v-model="search"
                             @input="onSearch"
                             type="search" icon="search">
                    </b-input>
                </b-field>
            @endif
        </div>
        <div class="level-right">
            <div class="field is-grouped">
                <p class="control">
                    <b-dropdown position="is-bottom-left">
                        <a class="button" slot="trigger">
                            <b-icon icon="play" size="small"></b-icon>
                        </a>

                        <b-dropdown-item>Action</b-dropdown-item>
                        <b-dropdown-item>Another action</b-dropdown-item>
                        <b-dropdown-item>Something else</b-dropdown-item>
                    </b-dropdown>
                </p>
                <p class="control filters">
                    <b-dropdown position="is-bottom-left">
                        <a class="button" slot="trigger">
                            <b-icon icon="filter" size="small"></b-icon>
                        </a>

                        <b-dropdown-item custom paddingless>
                            @foreach($type->filters() as $filter)
                                {{ $filter->render() }}
                            @endforeach
                        </b-dropdown-item>

                    </b-dropdown>
                </p>
                <p class="control">
                    <a class="button is-danger"
                       :disabled="! checkedRows.length"
                       @click.prevent="onDeleteSelected(checkedRows, '{{ $deleteManyRoute }}', '{{ str_plural($type->name) }}', '{{ $primaryKey }}')">
                        <b-icon icon="trash" size="small"></b-icon>
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>