<template>

    <div class="fa-repeater">

        <div class="table-wrapper">
            <table class="table is-bordered is-striped has-mobile-cards">

                <thead>
                <tr>
                    <th class="index-cell">#</th>
                    <th v-for="column in columns" :style="{ width: column.width + 'px' }">
                        <div class="th-wrap">
                            {{ column.label }}
                        </div>
                    </th>
                    <th class="actions-cell"></th>
                </tr>
                </thead>

                <tbody v-if="rows.length" v-dragula="rows" drake="repeater">

                <tr v-for="(row, index) in rows" :key="row.jsId">

                    <td class="index-cell" >
                        {{ index + 1 }}
                    </td>

                    <slot :row="row" :index="index"></slot>

                    <th class="actions-cell">
                        <button class="delete is-small"
                                @click.prevent="deleteRow(index, $event)"></button>
                    </th>

                </tr>

                </tbody>
                <tbody v-else>
                <tr>
                    <td :colspan="columns.length + 2">
                        <slot name="empty">
                            <div class="is-size-4 has-text-centered">
                                Click
                                <a @click.prevent="addRow">{{ addButtonText }}</a>
                                To Add {{ labelSingular }}
                            </div>
                        </slot>
                    </td>
                </tr>
                </tbody>

            </table>
        </div>

        <div class="level">
            <div class="level-left"></div>
            <div class="level-right">
                <button type="button" class="button" @click.prevent="addRow">
                    <span>{{ addButtonText }}</span>
                </button>
            </div>
        </div>

    </div>

</template>

<script>
    import Helpers from '../../Helpers/Helpers';

    export default {

        props: {
            value: {
                type: Array,
                required: true
            },
            form: {
                type: Object,
                required: true
            },
            label: {
                type: String,
                default: "Items"
            },
            labelSingular: {
                type: String,
                default: "Item"
            },
            addButtonText: {
                type: String,
                default: "Add Row"
            }
        },

        data() {
            return {
                _isRepeater: true,
                columns: []
            }
        },

        created() {
            if(this.value.length === 0) {
                this.addRow();

                this.$nextTick(function() {
                    this.deleteRow(0);
                });
            }
        },

        methods: {
            addRow() {
                this.value.push({
                    'jsId': Helpers.makeId()
                });
            },

            deleteRow(index) {
                this.$delete(this.value, index);
            }
        },

        computed: {
            rows() {
                return this.value;
            }
        }

    }
</script>