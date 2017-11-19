<template>

    <div class="la-options-list">
        <div v-if="options && options.length > 0">

            <template v-for="(option, index) in options">

                <la-option :props="option.props || {}"
                           :key="index"
                           :type="option.type"
                           :form-key="(formKey ? formKey + '.' : '') + option.key"
                           :option="option"
                           v-if="! isSubOption(option.key)"
                           @input="input"
                           v-model="newValue[option.key]">
                </la-option>

                <la-options-set v-else
                                :key="index"
                                :type="option.type"
                                :form-key="(formKey ? formKey + '.' : '') + getSubOptionKeys(option.key)[0]"
                                :options="[fixSubOptionKey(option)]"
                                v-model="newValue[getSubOptionKeys(option.key)[0]]">
                </la-options-set>

            </template>

        </div>
    </div>

</template>

<script>
    import ParentFormMixin from '../../Mixins/ParentForm';
    import LaOption from './Option.vue';
    import {cloneDeep} from "lodash";

    export default {

        name: 'la-options-set',

        mixins: [ParentFormMixin],

        props: {
            value: Object,
            type: String,
            formKey: String,
            options: [Array, Object]
        },

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
            input(value) {
                this.$emit('input', this.newValue);
            },

            isSubOption(key) {
                key = key.split('.');

                return key.length > 1;
            },

            getSubOptionKeys(key) {
                key = key.split('.');
                let firstKey = key.shift();

                return [firstKey, key.join('.')];
            },

            fixSubOptionKey(subOption) {
                let subOptionClone = cloneDeep(subOption);

                subOptionClone.key = this.getSubOptionKeys(subOption.key)[1];

                return subOptionClone;
            }

        },

        beforeCreate: function () {
            this.$options.components.LaOption = require('./Option.vue')
        }

    }

</script>