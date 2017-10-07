<template>

    <div class="fa-relationship" :class="{'is-loading': loading}">

        <form class="search-control" @submit.prevent>
            <b-field>
                <b-input :placeholder="'Search ' + label"
                         v-model="search"
                         type="search"
                         :loading="loading"
                         @input="onSearch"
                         icon="search">
                </b-input>
            </b-field>
        </form>

        <div class="relation-lists">

            <div class="new-list">
                <b-loading :active="loading" :canCancel="false"></b-loading>

                <la-relationship-row v-for="row in rows"
                                     :label="row.label"
                                     :extra-labels="row.extra_labels"
                                     :image="row.image"
                                     :edit-link="showEditButton ? row.edit_link : null"
                                     :is-selected="isSelected(row)"
                                     :key="row.key">

                    <button class="button is-small is-success"
                            @click.prevent="selectRow(row)"
                            type="button">
                        <b-icon icon="plus"></b-icon>
                    </button>

                </la-relationship-row>


                <p class="has-text-centered" v-if="rows.length <= 0 && ! this.loading">
                    No {{ label }} found.
                </p>

            </div>

            <div class="selected-list">
                <la-relationship-row v-for="(row, index) in selectedRows"
                                     :label="row.label"
                                     :extra-labels="row.extra_labels"
                                     :image="row.image"
                                     :edit-link="showEditButton ? row.edit_link : null"
                                     :key="row.key">

                    <button class="button is-small is-danger"
                            @click.prevent="deleteRow(row, index)"
                            type="button">
                        <b-icon icon="remove"></b-icon>
                    </button>

                </la-relationship-row>

                <p class="has-text-centered" v-if="selectedRows.length <= 0">
                    No {{ label }} Selected,<br>
                    You can add {{ label }} from the list at the left
                </p>

            </div>
        </div>

        <div class="pagination-control">
            <div class="level">
                <div class="level-left">
                    <div class="level-item">
                        <b-pagination
                                :total="total"
                                :current.sync="page"
                                order="is-centered"
                                size="is-small"
                                :per-page="perPage"
                                @change="onPageChange"
                                simple>
                        </b-pagination>
                    </div>
                </div>

                <div class="level-right" v-if="showCreateButton && createButtonLink">
                    <div class="level-item">
                        <a :href="createButtonLink"
                           class="button is-success"
                           target="_blank">
                            <b-icon icon="plus"></b-icon>
                            <span>Create new {{ labelSingular.toLowerCase() }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>

</template>

<script>
    import Helpers from '../../Helpers/Helpers';
    import LaHttp from '../../Forms/LaHttp';
    import LaRelationshipRow from './RelationshipRow.vue';

    export default {

        props: {
            value: {
                type: Array,
                required: true
            },
            form: {
                type: Object,
                required: true
            },
            queryUri: {
                type: String,
                required: true
            },
            label: {
                type: String,
                default: "Items"
            },
            labelSingular: {
                type: String,
                default: "Item"
            },
            showCreateButton: {
                type: Boolean,
                default: false
            },
            showEditButton: {
                type: Boolean,
                default: false
            },
            createButtonLink: {
                type: String,
                default: null
            }
        },

        data() {
            return {
                _isRelationship: true,
                search: "",
                rows: [],
                page: 1,
                selectedRows: [],
                total: 0,
                hasImage: false,
                perPage: 15,
                loading: false
            }
        },

        created() {
            this.fetchItems();
            this.selectedRows = this.value ? this.value : [];
        },

        methods: {
            fetchItems() {
                this.loading = true;
                this.rows = [];

                LaHttp.get(this.queryUri, {
                    search: this.search,
                    page: this.page
                })
                .then(res => {
                    let data = res.data.data;

                    this.rows = data.data;
                    this.total = data.total;
                    this.hasImage = data.has_image;
                    this.perPage = data.per_page;
                    this.loading = false;
                })
                .catch(err => {

                });
            },

            onSearch() {
                this.loading = true;
                this.page = 1;

                this.$nextTick(function() {
                    this.fetchItems();
                });
            },

            onPageChange() {
                this.loading = true;

                this.$nextTick(function() {
                    this.fetchItems();
                });
            },

            selectRow(row) {
                if(this.isSelected(row)) {
                    return;
                }

                this.selectedRows.push(row);

                this.$nextTick(function () {
                    this.$emit('input', this.selectedRows);
                });
            },

            deleteRow(row, rowIndex) {
                if(! this.isSelected(row)) {
                    return;
                }

                this.$delete(this.selectedRows, rowIndex);

                this.$nextTick(function () {
                    this.$emit('input', this.selectedRows);
                });
            },


            isSelected(row) {
                for (let rowIndex in this.selectedRows) {
                    if(row.key === this.selectedRows[rowIndex]['key']) {
                        return true;
                    }
                }

                return false;
            }

        },

        computed: {

        },

        components: {
            LaRelationshipRow
        }

    }
</script>