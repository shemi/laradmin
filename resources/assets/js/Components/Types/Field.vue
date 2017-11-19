<template>

    <vddl-nodrag class="no-drag la-field"
                 :class="{'is-open': isOpen}">

            <div class="level la-field-header" @click.prevent="isOpen = ! isOpen">
                <div class="level-left">
                    <div class="level-item">
                        <vddl-handle
                                :handle-left="20"
                                :handle-top="20"
                                class="handle">
                            <b-icon icon="arrows"></b-icon>
                        </vddl-handle>
                    </div>
                    <div class="level-item" @click.stop>
                        <b-dropdown>
                            <button class="button is-small" slot="trigger">
                                <b-icon icon="ellipsis-v" size="is-small"></b-icon>
                            </button>

                            <b-dropdown-item @click="cloneField">Clone</b-dropdown-item>
                            <b-dropdown-item separator>Clone</b-dropdown-item>
                            <b-dropdown-item class="has-text-danger" @click="deleteField">Delete</b-dropdown-item>
                        </b-dropdown>
                    </div>
                    <div class="level-item">
                        <p class="la-panel-header-title title is-4">
                            {{ newValue.label || 'New Field' }}
                        </p>
                    </div>
                    <div class="level-item">
                        <p class="la-panel-header-extra">
                            key: {{ newValue.key || 'NULL' }} <b>|</b>
                            type: {{ displayType || 'NULL' }} <b>|</b>
                            id: {{ newValue.id || 'NULL' }}
                        </p>
                    </div>
                </div>

                <div class="level-right">
                    <div class="level-item">
                        <a class="panel-content-toggle">
                            <b-icon :icon="isOpen ? 'caret-down' : 'caret-up'"></b-icon>
                        </a>
                    </div>
                </div>
            </div>

            <div class="field-options-set" v-if="isOpen">
                <div class="columns">
                    <div class="column">
                        <la-option :form-key="formKey + '.label'"
                                   :props="{'type': 'text'}"
                                   v-model="newValue.label"
                                   :option="{'label': 'Label'}"
                                   type="b-input">
                        </la-option>
                    </div>
                    <div class="column">
                        <la-option :form-key="formKey + '.key'"
                                   :props="{'type': 'text'}"
                                   v-model="newValue.key"
                                   :option="{'label': 'Key'}"
                                   type="b-input">
                        </la-option>
                    </div>
                </div>

                <div class="columns">
                    <div class="column">
                        <b-field label="Field Type">
                            <b-select v-model="newType"
                                      expanded
                                      @input="changeSchema"
                                      placeholder="Select a type">
                                <option v-for="(sub, key) in types"
                                        :value="key">
                                    {{ key }}
                                </option>
                            </b-select>
                        </b-field>
                    </div>
                    <div class="column">
                        <b-field label="Field Sub Type" v-if="subTypes">
                            <b-select v-model="newValue.template_options.type"
                                      expanded
                                      placeholder="Select a type">
                                <option v-for="key in subTypes"
                                        :value="key">
                                    {{ key }}
                                </option>
                            </b-select>
                        </b-field>
                    </div>
                </div>

                <b-field label="Visibility">
                    <div class="block">
                        <b-checkbox v-model="newValue.visibility"
                                    v-for="view in screens"
                                    :key="view"
                                    :native-value="view">
                            {{ view }}
                        </b-checkbox>
                    </div>
                </b-field>

                <la-options-set :type="newValue.type"
                                :form-key="formKey"
                                :options="options"
                                v-model="newValue">
                </la-options-set>

                <b-field label="Validation" v-if="! value.read_only">
                    <la-validation-set v-model="newValue.validation">
                    </la-validation-set>
                </b-field>
            </div>

    </vddl-nodrag>

</template>

<script>
    import {cloneDeep, isUndefined} from 'lodash';
    import ParentFormMixin from '../../Mixins/ParentForm';
    import Helpers from '../../Helpers/Helpers';

    export default {

        name: 'la-field',

        mixins: [ParentFormMixin],

        props: {
            formKey: String,
            value: Object
        },

        data() {
            return {
                types: window.laradmin.types,
                isOpen: false,
                newValue: this.value,
                newType: null,
                newSubType: null,
                windowData: cloneDeep(window.laradmin.schemas)
            }
        },

        beforeCreate() {
            this.$options.components.LaOptionsSet = require('./OptionsSet.vue');
            this.$options.components.LaOption = require('./Option.vue');
            this.$options.components.LaValidationSet = require('./ValidationSet.vue');
        },

        created() {
            this.initField();
        },

        watch: {
            value(newValue) {
                this.newValue = newValue;
            },
            newValue(newValue) {
                this.$emit('input', newValue);
            }
        },

        methods: {

            initField() {
                this.newType = this.type;
                this.newSubType = this.subType;

                if(! this.newValue.id) {
                    this.newValue.id = Helpers.makeId();

                    this.$emit('input', this.newValue);
                }

                this.checkSchema();
            },

            checkSchema() {
                let schemaKeys = Object.keys(this.schema),
                    schemaKeyIndex,
                    schemaKey;

                for (schemaKeyIndex in schemaKeys) {
                    schemaKey = schemaKeys[schemaKeyIndex];

                    if(isUndefined(this.newValue[schemaKey])) {
                        this.$set(this.newValue, schemaKey, this.schema[schemaKey]);
                    }
                }

                this.$emit('input', this.newValue);
            },

            changeSchema() {
                const keysToKeep = [
                    "label",
                    "key",
                    "validation",
                    "show_label",
                    "visibility",
                    "read_only",
                    "id",
                    "browse_settings"
                ];

                let newSchema = this.newSchema,
                    valueClone = cloneDeep(this.newValue),
                    newSchemaKeys = Object.keys(newSchema),
                    newSchemaKeyIndex,
                    key,
                    value;

                for (newSchemaKeyIndex in newSchemaKeys) {
                    key = newSchemaKeys[newSchemaKeyIndex];
                    value = newSchema[key];

                    if(keysToKeep.indexOf(key) >= 0) {
                        value = valueClone[key];
                    }

                    this.$set(this.newValue, key, cloneDeep(value));
                }

                this.$emit('input', this.newValue);
            },

            cloneField() {
                this.isOpen = false;

                this.$emit('clone');
            },

            deleteField() {
                this.isOpen = false;

                this.$emit('delete');
            }

        },

        computed: {
            type() {
                return this.newValue.type;
            },

            subType() {
                if(! this.newValue.template_options) {
                    return null;
                }

                return this.newValue.template_options.type;
            },

            displayType() {
                return this.subType ?
                    `${this.type}@${this.subType}` :
                    this.type;
            },

            subTypes() {
                return this.types[this.type];
            },

            data() {
                return this.windowData[this.type];
            },

            schema() {
                return this.data.schema;
            },

            options() {
                return this.data.options;
            },

            screens() {
                return this.data.visibility;
            },

            newData() {
                return window.laradmin.schemas[this.newType]
            },

            newSchema() {
                return this.newData.schema;
            }
        },

        components: {

        }

    }

</script>