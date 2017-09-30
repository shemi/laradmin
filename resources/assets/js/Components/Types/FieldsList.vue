<template>

    <div class="la-fields-list">
        <div>

            <div class="fields-list-container">
                <la-field v-for="(field, index) in newValue"
                          :form-key="formKey+'.'+index"
                          v-model="newValue[index]"
                          :key="index">
                </la-field>
            </div>

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
    import LaField from './Field.vue';
    import ParentFormMixin from '../../Mixins/ParentForm';
    import Helpers from '../../Helpers/Helpers';

    export default {
        name: 'la-fields-list',

        props: {
            value: Array,
            formKey: String
        },

        mixins: [ParentFormMixin],

        data() {
            return {
                newValue: this.value
            }
        },

        watch: {
            value(value) {
                this.newValue = value;
            }
        },

        methods: {
            createField() {
                let schema = JSON.parse(JSON.stringify(window.laradmin.schemas['input'].schema));
                schema.id = Helpers.makeId();

                this.newValue.push(schema);
                this.$emit('input', this.newValue);
            },

            input(value) {
                this.newValue = value;
                this.$emit('input', value);
            },
        },

        components: {
            LaField
        }

    }

</script>