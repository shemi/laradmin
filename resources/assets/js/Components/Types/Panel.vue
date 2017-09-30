<template>

    <div class="la-panel">

        <div class="level la-panel-header">
            <div class="level-left">
                <div class="level-item">
                    <div>
                        <p class="la-panel-header-title title is-4">
                            {{ panel.title }}
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

        <la-options-set type="panel"
                        :form-key="formKey"
                        v-model="panel"
                        v-if="isOpen"></la-options-set>

    </div>

</template>

<script>

    import ParentFormMixin from '../../Mixins/ParentForm';
    import LaOptionsSet from './OptionsSet.vue';

    export default {

        name: 'la-panel',

        mixins: [ParentFormMixin],

        props: {
            panel: Object,
            formKey: String,
        },

        data() {
            return {
                isOpen: true
            }
        },

        components: {
            LaOptionsSet
        }

    }

</script>