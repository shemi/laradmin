<template>

    <div class="fa-repeater">

        <div class="fa-repeater-blocks">

            <draggable :list="value" :options="dragOptions" @start="drag=true" @end="drag=false">
                <transition-group name="fa-repeater-blocks-list">
                    <la-repeater-row class="fa-repeater-block"
                                     v-for="(row, index) in rows"
                                     :row="row"
                                     :index="index"
                                     :collapse-field-key="collapseFieldKey"
                                     @delete-row="deleteRow"
                                     :key="row.jsId">

                        <slot :row="row" :index="index"></slot>

                    </la-repeater-row>
                </transition-group>
            </draggable>


            <div class="fa-repeater-blocks-footer level">
                <div class="level-left">
                    <button type="button"
                            class="button is-dark"
                            v-if="deleteHistory.length > 0"
                            @click.prevent="undoDelete">
                        <b-icon icon="undo"></b-icon>
                        <span>UNDO</span>
                    </button>
                </div>
                <div class="level-right">
                    <button type="button" class="button is-success" @click.prevent="addRow">
                        <b-icon icon="plus"></b-icon>
                        <span>{{ addButtonText }}</span>
                    </button>
                </div>
            </div>

        </div>


    </div>

</template>

<script>
    import Helpers from '../../Helpers/Helpers';
    import LaForm from '../../Forms/LaForm';
    import LaRepeaterRow from './RepeaterRow';
    import { cloneDeep } from 'lodash';
    import draggable from 'vuedraggable';

    export default {

        props: {
            value: {
                type: Array,
                required: true
            },
            formKey: {
                type: String,
                required: true
            },
            initColumns: {
                type: Object,
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
            },
            collapseFieldKey: {
                type: String,
                default: "id"
            },
            draggable: {
                type: Boolean,
                default: true
            }
        },

        watch: {
            value(newValue) {
                this.init();
            }
        },

        data() {
            return {
                _isRepeater: true,
                deleteHistory: [],
                drag: false
            }
        },

        created() {
            this.init();
        },

        methods: {
            init() {
                for(let row of this.value) {
                    if(! row.jsId) {
                        row.jsId = Helpers.makeId();
                    }
                }
            },

            addRow() {
                let row = {
                    'jsId': Helpers.makeId(),
                    'laResentCreated': true
                };

                for (let key of Object.keys(this.initColumns)) {
                    row[key] = (new LaForm({})).transformValue(row, key, this.formKey + ".");
                }

                this.value.push(row);
            },

            undoDelete() {
                if(! this.deleteHistory.length) {
                    return;
                }

                let index = this.deleteHistory.length - 1,
                    row = this.deleteHistory[index];

                this.value.splice(row.laOriginalPosition, 0, row);

                this.$delete(this.deleteHistory, index);
            },

            deleteRow(index) {
                let rowClone = cloneDeep(this.value[index]);
                rowClone.laOriginalPosition = index;
                rowClone.laResentCreated = false;
                this.deleteHistory.push(rowClone);

                this.$delete(this.value, index);
            }
        },

        computed: {
            rows() {
                return this.value;
            },

            dragOptions () {
                return  {
                    animation: 0,
                    group: 'repeater-' + this.formKey,
                    disabled: ! this.draggable,
                    ghostClass: 'ghost',
                    handle: '.la-drag-handle',
                    scroll: true,
                };
            }
        },

        components: {
            LaRepeaterRow,
            draggable
        }

    }
</script>