<template>

    <div class="la-field">

        <div class="level la-field-header">
            <div class="level-left">
                <div class="level-item">
                    <div>
                        <p class="la-panel-header-title title is-4">
                            {{ newValue.label }}
                        </p>

                        <div class="panel-header-actions">
                            <a class="is-small">Duplicate</a>
                            <a class="is-small has-text-danger">Delete</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="level-right">
                <div class="level-item">
                    <a class="panel-content-toggle" @click.prevent="isOpen = ! isOpen">
                        <b-icon :icon="isOpen ? 'caret-down' : 'caret-up'"></b-icon>
                    </a>
                </div>
            </div>
        </div>

        <div class="field-options-set">
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
                        <b-select v-model="newValue.type"
                                  expanded
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

            <la-options-set :type="newValue.type"
                            :form-key="formKey"
                            v-model="newValue">
            </la-options-set>
        </div>


    </div>

</template>

<script>

    import ParentFormMixin from '../../Mixins/ParentForm';

    export default {

        name: 'la-field',

        mixins: [ParentFormMixin],

        props: {
            type: String,
            formKey: String,
            value: Object
        },

        data() {
            return {
                isOpen: false,
                newValue: this.value,
                types: window.laradmin.types,
//                options: window.laradmin.schemas[this.type]['options']
            }
        },

        beforeCreate: function () {
            this.$options.components.LaOptionsSet = require('./OptionsSet.vue');
            this.$options.components.LaOption = require('./Option.vue');
        },

        computed: {
            subTypes() {
                return this.types[this.newValue.type];
            }
        },

        components: {

        }

    }

</script>