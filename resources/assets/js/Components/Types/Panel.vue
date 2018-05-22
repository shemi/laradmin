<template>

    <vddl-nodrag class="la-panel no-drag"
                 :class="{'is-open': isOpen}">

        <div class="level la-panel-header">
            <div class="level-left" @click.prevent="isOpen = ! isOpen">
                <div class="level-item">
                    <vddl-handle
                            :handle-left="20"
                            :handle-top="20"
                            class="handle">
                        <b-icon icon="arrows"></b-icon>
                    </vddl-handle>
                </div>

                <div class="level-item" v-if="hasErrors">
                    <b-icon icon="exclamation-circle"
                            type="is-danger"></b-icon>
                </div>

                <div class="level-item" @click.stop.prevent v-if="! isProtected">
                    <b-dropdown>
                        <button class="button is-small" slot="trigger" @click.prevent>
                            <b-icon icon="ellipsis-v" size="is-small"></b-icon>
                        </button>

                        <b-dropdown-item @click="clonePanel">Clone</b-dropdown-item>
                        <b-dropdown-item separator>Clone</b-dropdown-item>
                        <b-dropdown-item class="has-text-danger" @click="deletePanel">Delete</b-dropdown-item>
                    </b-dropdown>
                </div>

                <div class="level-item">
                    <div>
                        <p class="la-panel-header-title title is-4">
                            <span>{{ panel.title }}</span>
                            <b-tooltip label="Main meta panel"
                                       position="is-right"
                                       v-if="panel.is_main_meta">
                                <b-icon icon="star" size="is-small"></b-icon>
                            </b-tooltip>
                        </p>
                    </div>
                </div>
            </div>

            <div class="level-right">
                <div class="level-item">
                    <b-tooltip label="Select The Panel Position" position="is-left">
                        <b-field>
                            <b-radio-button v-model="panel.position"
                                            :disabled="isProtected"
                                            native-value="main">
                                <span>Main</span>
                            </b-radio-button>

                            <b-radio-button v-model="panel.position"
                                            :disabled="isProtected"
                                            native-value="side">
                                <span>Side</span>
                            </b-radio-button>
                        </b-field>
                    </b-tooltip>
                </div>
                <div class="level-item">
                    <a class="panel-content-toggle" @click.prevent="isOpen = ! isOpen">
                        <b-icon :icon="isOpen ? 'caret-down' : 'caret-up'"></b-icon>
                    </a>
                </div>
            </div>
        </div>

        <!--<la-options-set type="panel"-->
                        <!--:form-key="formKey"-->
                        <!--:options="options"-->
                        <!--v-model="panel"-->
                        <!--@has-errors="handelErrors"-->
                        <!--v-show="isOpen">-->
        <!--</la-options-set>-->

        <div class="la-options-list la-panel-form"
             :is="formComponent"
             :options="options"
             :form-key="formKey"
             v-model="panel"
             @input="input"
             @has-errors="handelErrors"
             v-show="isOpen">
        </div>

    </vddl-nodrag>

</template>

<script>
    import ParentFormMixin from '../../Mixins/ParentForm';
    import LaOptionsSet from './OptionsSet.vue';
    import { cloneDeep } from "lodash";

    import LaMainMetaPanelForm from './Panels/MainMetaPanelForm';
    import LaSimplePanelForm from './Panels/SimplePanelForm';
    import LaTabsPanelForm from './Panels/TabsPanelForm';

    export default {

        name: 'la-panel',

        mixins: [ParentFormMixin],

        props: {
            value: Object,
            formKey: String,
        },

        data() {
            return {
                isOpen: false,
                panel: this.value,
                type: this.value.type,
                builderData: cloneDeep(window.laradmin.builderData.panels[this.value.type]),
                hasErrors: false,
            }
        },

        methods: {
            input(value) {
                this.panel = value;
                this.$emit('input', value);
            },

            clonePanel() {
                this.isOpen = false;

                this.$emit('clone');
            },

            deletePanel() {
                this.isOpen = false;

                this.$emit('delete');
            },

            handelErrors(hasErrors) {
                this.hasErrors = hasErrors;
            }

        },

        computed: {
            options() {
                return this.builderData.options;
            },

            isProtected() {
                return this.builderData.protected;
            },

            formComponent() {
                return [
                    'la',
                    this.type.replace(/\_/g, '-'),
                    'panel-form'
                ].join('-');
            }
        },

        components: {
            LaOptionsSet,
            LaMainMetaPanelForm,
            LaSimplePanelForm,
            LaTabsPanelForm
        }

    }

</script>