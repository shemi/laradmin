<template>

    <div class="la-field">

        <div class="level la-field-header">
            <div class="level-left">
                <div class="level-item">
                    <div>
                        <p class="la-panel-header-title title is-4">
                            {{ newValue.name }}
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

        <la-options-set :type="newValue.type"
                        :form-key="formKey"
                        v-model="newValue"
                        v-if="isOpen"></la-options-set>

    </div>

</template>

<script>

    import ParentFormMixin from '../../Mixins/ParentForm';
    import LaOptionsSet from './OptionsSet.vue';

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
                isOpen: true,
                newValue: this.value,
//                options: window.laradmin.schemas[this.type]['options']
            }
        },

        components: {
            LaOptionsSet
        }

    }

</script>