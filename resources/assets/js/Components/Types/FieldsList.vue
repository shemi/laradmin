<template>

    <div class="la-fields-list">
        <div>

            <vddl-list class="fields-list-container"
                       :list="newValue"
                       :allowed-types="['fields']"
                       :drop="handleDrop"
                       :horizontal="false">

                <vddl-draggable v-for="(field, index) in newValue"
                                :key="field.id"
                                :draggable="field"
                                :index="index"
                                :wrapper="newValue"
                                type="fields"
                                v-show="! isHidden(field)"
                                :moved="handleMoved"
                                class="la-fields-list-item"
                                effect-allowed="move">

                    <la-field :form-key="formKey + '.' + index"
                              @clone="cloneField(index, field)"
                              @delete="deleteField(index, field, formKey + '.' + index)"
                              @has-errors="handelErrors($event, formKey + '.' + index, field)"
                              v-model="newValue[index]">
                    </la-field>

                </vddl-draggable>

                <vddl-placeholder>
                    <div class="placeholder-field">
                        Drop Here
                    </div>
                </vddl-placeholder>

            </vddl-list>

            <div class="fields-list-actions">
                <button type="button"
                        class="button button-primary"
                        @click.prevent="createField">
                    Add Field
                </button>
            </div>

        </div>
    </div>

</template>

<script>
    import {cloneDeep} from 'lodash';
    import LaField from './Field.vue';
    import ParentFormMixin from '../../Mixins/ParentForm';
    import Helpers from '../../Helpers/Helpers';

    export default {
        name: 'la-fields-list',

        props: {
            value: Array,
            formKey: String,
            setStructureBeforeCreate: Function,
            isHidden: {
                type: Function,
                default: (field) => false
            }
        },

        mixins: [ParentFormMixin],

        data() {
            return {
                newValue: this.value,
                builderData: window.laradmin.builderData,
                errors: {},
            }
        },

        watch: {
            value(value) {
                this.newValue = value;
            }
        },

        methods: {
            createField() {
                let structure = cloneDeep(this.builderData.fields.input.structure);
                structure.id = Helpers.makeId();

                if(typeof this.setStructureBeforeCreate === 'function') {
                    this.setStructureBeforeCreate(structure);
                }

                this.newValue.push(structure);
                this.$emit('input', this.newValue);
            },

            input(value) {
                this.newValue = value;
                this.$emit('input', value);
            },

            cloneField(index, field) {
                let clone = cloneDeep(field);

                clone.id = Helpers.makeId();
                clone.label = `${clone.label} (CLONE)`;

                this.newValue.splice(index + 1, 0, clone);
                this.input(this.newValue);
            },

            deleteField(index, field, key) {
                this.handelErrors(false, key);

                this.$delete(this.newValue, index);
                this.input(this.newValue);
            },

            handleDrop(data) {
                const { index, list, item } = data;

                item.id = Helpers.makeId();

                if(typeof this.setStructureBeforeCreate === 'function') {
                    this.setStructureBeforeCreate(item);
                }

                list.splice(index, 0, item);
            },

            handleMoved(item) {
                const { index, list } = item;
                list.splice(index, 1);
            },

            handelErrors(hasErrors, key, field) {
                this.errors[key] = hasErrors;

                this.$emit('has-errors', Object.values(this.errors).indexOf(true) >= 0);
                this.$emit('field-has-errors', {field, hasErrors});
            }

        },

        beforeCreate() {
            this.$options.components.LaField = require('./Field.vue')
        },

        components: {

        }

    }

</script>