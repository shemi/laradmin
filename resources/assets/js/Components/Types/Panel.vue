<template>

    <div class="la-panel"
         :class="{'is-open': isOpen}">

        <div class="level la-panel-header">
            <div class="level-left" @click.prevent="isOpen = ! isOpen">
                <div class="level-item">
                    <b-icon icon="arrows"></b-icon>
                </div>
                <div class="level-item">
                    <div>
                        <p class="la-panel-header-title title is-4">
                            <span>{{ panel.title }}</span>
                            <b-tooltip label="Main meta panel"
                                       position="is-right"
                                       v-if="panel.is_main_meta">
                                <b-icon icon="star"></b-icon>
                            </b-tooltip>
                        </p>

                        <div class="panel-header-actions" v-if="! panel.is_main_meta">
                            <a class="is-small">Duplicate</a>
                            <a class="is-small has-text-danger">Delete</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="level-right">
                <div class="level-item">
                    <b-tooltip label="Select The Panel Position" position="is-left">
                        <b-field>
                            <b-radio-button v-model="panel.position"
                                            :disabled="panel.is_main_meta"
                                            native-value="main">
                                <span>Main</span>
                            </b-radio-button>

                            <b-radio-button v-model="panel.position"
                                            :disabled="panel.is_main_meta"
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

        <la-options-set type="panel"
                        :form-key="formKey"
                        :options="options"
                        v-model="panel"
                        v-show="isOpen">
        </la-options-set>

    </div>

</template>

<script>
    import ParentFormMixin from '../../Mixins/ParentForm';
    import LaOptionsSet from './OptionsSet.vue';
    import { cloneDeep } from "lodash";

    export default {

        name: 'la-panel',

        mixins: [ParentFormMixin],

        props: {
            panel: Object,
            formKey: String,
        },

        data() {
            return {
                isOpen: false,
                options: cloneDeep(window.laradmin.builderData.panels.panel.options)
            }
        },

        components: {
            LaOptionsSet
        }

    }

</script>