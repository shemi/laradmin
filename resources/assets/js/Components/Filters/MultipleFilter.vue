<template>

    <b-field class="multiple-filter" :label="label">

        <b-taginput
                v-model="newValue"
                :data="filteredOptions"
                autocomplete
                field="key"
                placeholder="search"
                attached
                @input="onChange"
                @focus="fetch">

            <template slot-scope="props">
                {{ props.option.label }}
            </template>

            <template slot="empty">
                There are no items
            </template>
        </b-taginput>

    </b-field>

</template>

<script>
    import FilterMixin from "./FilterMixin";
    import _ from 'lodash';

    export default {

        name: 'la-multiple-filter',

        mixins: [FilterMixin],

        data() {
            return {

            }
        },

        methods: {
            transformValue(value) {
                return _.toArray(value);
            },

            onSelect(option) {

            },

            clear() {
                this.newValue = [];
                this.onChange();
            }

        }

    }

</script>