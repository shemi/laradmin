<template>

    <b-field :type="hasErrors ? 'is-danger' : ''"
             :class="'field-' + type"
             :messag="errorMessage"
             :label="option.label">

        <component :is="type"
                   v-bind="props"
                   v-model="newValue"
                   @input="input"
                   @blur="$emit('blur', $event)"
                   @focus="$emit('focus', $event)">
        </component>

    </b-field>

</template>

<script>
    import ParentFormMixin from '../../Mixins/ParentForm';
    import LaFieldsList from './FieldsList.vue';

    export default {
        name: 'la-option',

        props: {
            formKey: String,
            type: String,
            value: [String, Boolean, Object, Array],
            props: Object,
            option: Object
        },

        mixins: [ParentFormMixin],

        data() {
            return {
                newValue: this.value
            }
        },

        mounted() {

        },

        watch: {
            value(value) {
                this.newValue = value;
            }
        },

        methods: {
            input(value) {
                this.newValue = value;
                this.$emit('input', value);
            },
        },

        computed: {
            hasErrors() {
                return this.form.errors.has(this.formKey);
            },

            errorMessage() {
                if(! this.hasErrors) {
                    return null;
                }

                return this.form.errors.get(this.formKey);
            }
        },

        beforeCreate: function () {
            this.$options.components.LaFieldsList = require('./FieldsList.vue')
        },

        components: {

        }

    }

</script>