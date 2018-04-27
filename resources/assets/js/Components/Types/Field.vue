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
                <div class="level-item" @click.stop.prevent>
                    <b-dropdown>
                        <button class="button is-small" slot="trigger" @click.prevent>
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

        <div class="field-options-set" v-show="isOpen">

            <div class="columns">
                <div class="column">
                    <b-field label="Field Type">
                        <b-select v-model="newType"
                                  expanded
                                  @input="changeStructure"
                                  placeholder="Select a type">
                            <option v-for="(name, key) in types"
                                    :value="key">
                                {{ name }}
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

            <json-editor v-model="editorObject"
                         @input="updateValueFromEditorObject"
                         :on-editable="isNodeEditable"
                         :schema="schema">
            </json-editor>

            <b-field label="Fields"
                     class="field-la-fields-list"
                     v-if="data.supportSubFields">
                <la-fields-list v-model="newValue.fields"
                                :form-key="formKey + '.fields'">
                </la-fields-list>
            </b-field>

        </div>

    </vddl-nodrag>

</template>

<script>
    import {cloneDeep, isUndefined, defaults} from 'lodash';
    import ParentFormMixin from '../../Mixins/ParentForm';
    import Helpers from '../../Helpers/Helpers';
    import JsonEditor from '../JsonEditor/JsonEditor.vue';


    export default {

        name: 'la-field',

        mixins: [ParentFormMixin],

        props: {
            formKey: String,
            value: Object
        },

        data() {
            return {
                isField: true,
                isOpen: false,
                newValue: this.value,
                editorObject: {},
                newType: null,
                newSubType: null,
                builderData: cloneDeep(window.laradmin.builderData.fields)
            }
        },

        beforeCreate() {
            this.$options.components.LaOptionsSet = require('./OptionsSet.vue');
            this.$options.components.LaOption = require('./Option.vue');
            this.$options.components.LaValidationSet = require('./ValidationSet.vue');
            this.$options.components.laFieldsList = require('./FieldsList.vue');
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

                if (!this.newValue.id) {
                    this.newValue.id = Helpers.makeId();

                    this.$emit('input', this.newValue);
                }

                this.checkStructure();
                this.updateEditorObject();
            },

            checkStructure() {
                defaults(this.newValue, this.structure);

                this.$emit('input', this.newValue);
            },

            changeStructure() {
                const keysToKeep = [
                    "label",
                    "key",
                    "validation",
                    "show_label",
                    "id",
                    "options",
                    "browse_settings"
                ];

                let newStructure = this.newStructure,
                    valueClone = cloneDeep(this.newValue),
                    newStructureKeys = Object.keys(newStructure),
                    newStructureKeyIndex,
                    key,
                    value;

                for (newStructureKeyIndex in newStructureKeys) {
                    key = newStructureKeys[newStructureKeyIndex];
                    value = newStructure[key];

                    if (keysToKeep.indexOf(key) >= 0 && ! isUndefined(valueClone[key])) {
                        value = valueClone[key];
                    }

                    this.$set(this.newValue, key, cloneDeep(value));
                }

                this.updateEditorObject();
                this.$emit('input', this.newValue);
            },

            updateEditorObject() {
                let leaveOutKeys = ['id', 'type', 'fields'],
                    newValueKeys = Object.keys(this.newValue),
                    i, key,
                    editorObject = {};

                for(i in newValueKeys) {
                    key = newValueKeys[i];

                    if(leaveOutKeys.indexOf(key) >= 0) {
                        continue;
                    }

                    editorObject[key] = this.newValue[key];
                }

                this.$nextTick(function () {
                    this.editorObject = editorObject;
                });
            },

            updateValueFromEditorObject() {
                let leaveOutKeys = ['id', 'type', 'fields'],
                    editorObjectKeys = Object.keys(this.editorObject),
                    i, key;

                for (i in editorObjectKeys) {
                    key = editorObjectKeys[i];

                    if(leaveOutKeys.indexOf(key) >= 0) {
                        continue;
                    }

                    this.newValue[key] = this.editorObject[key];
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
            },

            isNodeEditable(node) {
                let field = node.path ? node.path.join('.') : false,
                    readOnlyFields = [
                        'id',
                        'type',
                        'template_options.type',
                        'fields'
                    ],
                    roles = {
                        field: true,
                        value: true
                    };

                if(! field) {
                    return true;
                }

                if(readOnlyFields.indexOf(field) >= 0) {
                    roles.field = false;
                    roles.value = false;
                }

                return roles;
            }

        },

        computed: {
            type() {
                return this.newValue.type;
            },

            subType() {
                if (! this.newValue.template_options) {
                    return null;
                }

                return this.newValue.template_options.type;
            },

            displayType() {
                return this.subType ?
                    `${this.type}@${this.subType}` :
                    this.type;
            },

            types() {
                let types = {},
                    typeKey,
                    type;

                for(typeKey in this.builderData) {
                    type = this.builderData[typeKey];

                    types[typeKey] = type.name;
                }

                return types;
            },

            data() {
                return this.builderData[this.type];
            },

            subTypes() {
                return this.data.subTypes;
            },

            schema() {
                return this.data.schema;
            },

            structure() {
                return this.data.structure;
            },

            screens() {
                return this.data.visibility;
            },

            newData() {
                return this.builderData[this.newType]
            },

            newSchema() {
                return this.newData.schema;
            },

            newStructure() {
                return this.newData.structure;
            }
        },

        components: {
            JsonEditor
        }

    }

</script>