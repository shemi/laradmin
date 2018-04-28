<template>

    <div class="la-column">
        <div @click.prevent="onEdit">
            <div class="label">{{ label }}</div>

            <div class="indicators">
                <b-icon icon="sort" size="is-small" v-if="sortable"></b-icon>
                <b-icon icon="search" size="is-small" v-if="searchable"></b-icon>
            </div>
        </div>
    </div>

</template>

<script>
    import ParentFormMixin from '../../Mixins/ParentForm';
    import Helpers from '../../Helpers/Helpers';
    import ColumnForm from './ColumnForm';

    export default {
        name: 'la-column',

        mixins: [ParentFormMixin],

        props: {
            value: Object
        },

        data() {
            return {
                newValue: this.value
            }
        },

        watch: {
            value(newValue) {
                this.newValue = newValue;
            }
        },

        methods: {
            updateBrowseSettings(settings) {
                this.$set(this.newValue, 'browse_settings', settings);

                this.$emit('input', this.newValue);
            },

            onEdit() {
                this.$modal.open({
                    parent: this,
                    width: 500,
                    props: {
                      'value': this.newValue
                    },
                    events: {
                        update: this.updateBrowseSettings
                    },
                    component: ColumnForm,
                    hasModalCard: true
                });
            }

        },

        computed: {
            label() {
                return this.newValue.browse_settings.label || this.newValue.label;
            },

            sortable() {
                return this.newValue.browse_settings.sortable;
            },

            searchable() {
                return this.newValue.browse_settings.searchable;
            }
        }

    }

</script>