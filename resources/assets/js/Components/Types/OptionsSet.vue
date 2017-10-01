<template>

    <div class="la-options-list">
        <div v-if="options.length > 0">

            <la-option v-for="(option, index) in options"
                       :props="option.props || {}"
                       :type="option.type"
                       :form-key="formKey + '.' + option.key"
                       :key="index"
                       :option="option"
                       @input="input"
                       v-model="newValue[option.key]">

            </la-option>

        </div>
    </div>

</template>

<script>

    import ParentFormMixin from '../../Mixins/ParentForm';
    import LaOption from './Option.vue';

    export default {

        name: 'la-options-set',

        mixins: [ParentFormMixin],

        props: {
            value: Object,
            type: String,
            formKey: String
        },

        data() {
            return {
                newValue: this.value,
                options: window.laradmin.schemas[this.type]['options']
            }
        },

        watch: {
            value(value) {
                this.newValue = value;
            }
        },

        methods: {
            input(value) {
                this.newValue = this.value;
                this.$emit('input', this.value);
            },
        },

        beforeCreate: function () {
            this.$options.components.LaOption = require('./Option.vue')
        }

    }

</script>