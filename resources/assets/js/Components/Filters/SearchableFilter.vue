<template>

    <b-field class="searchable-filter" :label="label">

        <b-field :class="{'clearable-autocomplete': true, 'is-loading': loading}">
            <b-autocomplete
                    v-model="search"
                    placeholder="search"
                    :keep-first="true"
                    :open-on-focus="true"
                    :data="filteredOptions"
                    field="key"
                    expanded
                    :loading="loading"
                    ref="autocomplate"
                    @focus="fetch"
                    @select="onSelect">
                <template slot-scope="props">
                    <span>{{ props.option.label }}</span>
                </template>

                <template slot="empty">
                    There are no items
                </template>

            </b-autocomplete>

            <a class="clear-autocomplete"
                    v-if="! loading && this.newValue && this.newValue.key"
                    @click="clear">
                <b-icon icon="times"></b-icon>
            </a>

        </b-field>


    </b-field>

</template>

<script>
    import FilterMixin from "./FilterMixin";

    export default {

        name: 'la-searchable-filter',

        mixins: [FilterMixin],

        data() {
            return {
                ignoreSelect: false
            }
        },

        watch: {
            value() {
                if(this.value && this.value.label) {
                    this.ignoreSelect = true;
                    this.$refs.autocomplate.setSelected(this.value);
                    this.$nextTick(() => {
                        this.ignoreSelect = false;
                    });
                }
            }
        },

        methods: {
            onSelect(option) {
                if(this.ignoreSelect) {
                    return;
                }

                this.newValue = option;
                this.onChange();
            },

            clear() {
                console.log('clear');

                this.$refs.autocomplate.setSelected(null, true);
                this.search = "";
                this.onSelect(null);
            }

        }

    }

</script>