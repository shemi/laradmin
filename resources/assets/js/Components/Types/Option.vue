<template>

    <b-field :type="hasErrors ? 'is-danger' : ''"
             :class="'field-' + type"
             :messag="errorMessage"
             :label="option.label">

        <component :is="type"
                   v-bind="newProps"
                   v-model="newValue"
                   :form-key="formKey"
                   @input="input"
                   @has-errors="$emit('has-errors', $event)"
                   @blur="$emit('blur', $event)"
                   @focus="$emit('focus', $event)">

            <la-dynamic-render v-if="option.slot"
                               :form-key="formKey"
                               :form="form"
                               :field="newValue"
                               :template="option.slot">
            </la-dynamic-render>

        </component>

    </b-field>

</template>

<script>
    import ParentFormMixin from '../../Mixins/ParentForm';
    import ParentFieldMixin from '../../Mixins/ParentField';
    import {get} from 'lodash';

    export default {
        name: 'la-option',

        props: {
            formKey: String,
            type: String,
            fieldType: String,
            value: [String, Boolean, Object, Array],
            props: Object,
            option: Object
        },

        mixins: [ParentFormMixin, ParentFieldMixin],

        data() {
            return {
                newValue: this.value,
                newProps: {}
            }
        },

        mounted() {
            this.setProps();
        },

        updated() {
            this.setProps();
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

            setProps() {
                let propsKeys = Object.keys(this.props);

                if(propsKeys.length <= 0) {
                    return;
                }

                for (let keyIndex in propsKeys) {
                    let key = propsKeys[keyIndex];
                    let val = this.props[key];

                    if(key.indexOf(':') === 0) {
                        key = key.replace(':', '');
                        val = new Function('return ' + val).bind(this);
                        val = val();
                    }

                    this.$set(this.newProps, key, val);
                }
            }

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
            this.$options.components.LaFieldsList = require('./FieldsList.vue');
            this.$options.components.LaTabsBuilder = require('./TabsBuilder.vue');
            this.$options.components.LaDynamicRender = require('./DynamicRender.vue');
        },

        components: {

        }

    }

</script>