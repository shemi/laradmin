<div class="la-crud-table-controls">

    <div class="tabs">
        <ul>
            <li @click="setQueryState()" :class="{'is-active': !isTrash}">
                <a>{{ trans('Index') }}</a>
            </li>
            @if($type->soft_deletes)
                <li @click="setQueryState(true)" :class="{'is-active': isTrash}">
                    <a>{{ trans('Trashed') }}</a>
                </li>
            @endif
        </ul>
    </div>

    <div class="level">
        <div class="level-left">
            @if($type->searchable_fields->isNotEmpty())
                <p class="level-item">
                    <b-input placeholder="Search..."
                             rounded
                             v-model="search"
                             @input="onSearch"
                             type="search" icon="search">
                    </b-input>
                </p>
            @endif
            <p class="level-item">
                <b-checkbox v-model="selectAllMatching" @change.native="onSelectAllMatchingChanged">
                    Select All Matching
                </b-checkbox>
            </p>
        </div>
        <div class="level-right">
            <div class="field is-grouped">
                <p class="control model-actions" v-if="controls.actions.length > 0">
                    <b-dropdown position="is-bottom-left" :disabled="! selectAllMatching && checkedRows.length <= 0">
                        <a class="button" slot="trigger">
                            <b-icon icon="play" size="small"></b-icon>
                        </a>

                        <b-dropdown-item v-for="action in controls.actions"
                                         :key="action.name"
                                         @click="applyAction(action, '{{ $primaryKey }}')">
                            @{{ action.label }}
                        </b-dropdown-item>

                    </b-dropdown>
                </p>
                @if($type->filters()->isNotEmpty())
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
                @endif
                @if(Laradmin::user()->can('delete ' . $type->slug))
                    <p class="control">
                        <a class="button is-danger"
                           :disabled="! selectAllMatching && checkedRows.length <= 0"
                           @click.prevent="onDeleteSelected(checkedRows, '{{ $deleteManyRoute }}', '{{ str_plural($type->name) }}', '{{ $primaryKey }}')">
                            <b-icon icon="trash" size="small"></b-icon>
                        </a>
                    </p>
                @endif
                <template v-if="isTrash">
                    @if(Laradmin::user()->can('restore ' . $type->slug))
                        <p class="control">
                            <a class="button is-info"
                               :disabled="! selectAllMatching && checkedRows.length <= 0"
                               @click.prevent="onRestoreSelected(checkedRows, '{{ $restoreManyRoute }}', '{{ str_plural($type->name) }}', '{{ $primaryKey }}')">
                                <b-icon icon="undo" size="small"></b-icon>
                            </a>
                        </p>
                    @endif
                </template>
            </div>
        </div>
    </div>
</div>
