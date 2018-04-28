<template>

    <div class="la-column-list-editor">
        <div>

            <vddl-list class="la-columns-list-container"
                       :list="columns"
                       :allowed-types="['column']"
                       :drop="handleDrop"
                       :vertical="false"
                       :horizontal="true">

                <vddl-draggable v-for="(column, index) in columns"
                                :key="column.id"
                                :draggable="column"
                                :index="index"
                                :wrapper="columns"
                                type="column"
                                :moved="handleMoved"
                                class="la-column-list-item"
                                effect-allowed="move">

                    <la-column v-model="columns[index]" @input="update"></la-column>

                </vddl-draggable>
            </vddl-list>

            <vddl-placeholder>
                <div class="placeholder la-column">
                    <div>
                        <div class="label">Drop Here</div>
                    </div>
                </div>
            </vddl-placeholder>

        </div>
    </div>

</template>

<script>
    import {cloneDeep, sortBy} from 'lodash';
    import ParentFormMixin from '../../Mixins/ParentForm';
    import Helpers from '../../Helpers/Helpers';
    import LaColumn from './Column';

    export default {
        name: 'la-column-list-editor',

        props: {
            'fields': Array,
            'view': String
        },

        mixins: [ParentFormMixin],

        data() {
            return {
                builderData: window.laradmin.builderData,
                columns: []
            }
        },

        watch: {
            fields: {
                handler(input) {
                    this.setColumns(input);
                },
                deep: true,
                immediate: true
            }
        },

        methods: {

            setColumns(fields) {
                const columns = fields
                    .filter((column) => {
                        return !column.visibility || column.visibility.indexOf(this.view) >= 0;
                    });

                this.columns = sortBy(columns, (column) => {
                    return column[this.view + '_settings']['order'];
                });
            },

            handleDrop(data) {
                const {index, list, item} = data;

                if (!item.originalId) {
                    this.$set(item, 'originalId', item.id);
                }

                this.$set(item, 'id', Helpers.makeId());

                list.splice(index, 0, item);
            },

            handleMoved(item) {
                const {index, list} = item;
                list.splice(index, 1);

                this.updateColumnsOrder();
            },

            update() {
                let column,
                    columnIndex,
                    field,
                    id;

                for (columnIndex in this.columns) {
                    column = this.columns[columnIndex];
                    id = column.originalId || column.id;

                    for (field of this.fields) {
                        if (id !== field.id) {
                            continue;
                        }

                        this.$set(field, this.view + '_settings', column[this.view + '_settings']);

                        if(field.forceUpdate) {
                            field.forceUpdate();
                        }
                    }
                }
            },

            updateColumnsOrder() {
                let column,
                    columnIndex,
                    field,
                    id;

                for (columnIndex in this.columns) {
                    column = this.columns[columnIndex];
                    id = column.originalId || column.id;

                    for (field of this.fields) {
                        if (id !== field.id) {
                            continue;
                        }

                        this.$set(field[this.view + '_settings'], 'order', parseInt(columnIndex));

                        if(field.forceUpdate) {
                            field.forceUpdate();
                        }
                    }
                }
            }
        },

        components: {
            LaColumn
        }

    }

</script>