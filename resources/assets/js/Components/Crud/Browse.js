import MixinsLoader from '../../Helpers/MixinsLoader';
import FormatDate from '../../Mixins/FormatDate';
import LaHttp from '../../Forms/LaHttp';
import {LaTable, LaTableColumn} from '../Table';
import deleteMixin from '../../Mixins/Delete';
import ServerError from '../../Mixins/ServerError';
import SimpleRouter from '../../Mixins/SimpleRouter';
import LaFieldRenderer from  '../FieldRenderer/FieldRenderer';
import Filters from '../Filters/index';
import {map} from 'lodash';

export default {
    mixins: MixinsLoader.load(
        'crudBrowse',
        [FormatDate, deleteMixin, ServerError, SimpleRouter]
    ),

    props: ['typeName', 'typeSlug', 'filterableFields'],

    data() {
        return {
            loading: true,
            checkedRows: [],
            selected: {},
            search: "",
            searchClock: null,
            controls: window.laradmin.controls,
            selectAllMatching: false,
            isTrash: false,
            query: {
                order_by: null,
                order: null,
                filters: {},
                page: 1,
                search: "",
                trashedOnly: 0
            },
            data: {
                data: []
            }
        }
    },

    mounted() {
        this.usePushState();

        if (this.query.trashedOnly) {
            this.isTrash = true;
        }
    },

    created() {
        for(let key of Object.keys(this.controls.filters)) {
            this.$set(this.query.filters, key, null);
        }
    },

    watch: {

    },

    methods: {

        setQueryState(trashed = false) {
            this.isTrash = trashed;
            this.query.trashedOnly = trashed ? 1 : 0;
            this.query.page = 1;
            this.data = {
                data: []
            };

            this.pushState(this.query);
        },

        applyAction(action, primaryKey) {
            const la_primary_keys = map(this.checkedRows, primaryKey);

            if(action.destructive) {
                const count = this.selectAllMatching ? this.data.total : la_primary_keys.length;
                const message = action.destructive.message.replace(/\{count\}/gm, count);

                this.$dialog.confirm({
                    message,
                    confirmText: action.destructive.ok,
                    cancelText: action.destructive.cancel,
                    type: 'is-danger',
                    hasIcon: true,
                    onConfirm: () => this.runAction(action, la_primary_keys)
                })
            } else {
                this.runAction(action, la_primary_keys);
            }
        },

        runAction(action, la_primary_keys = []) {
            this.loading = true;

            LaHttp.client().post(LaHttp.uri(`/actions/${this.typeSlug}/${action.name}/apply`), {
                ...this.query,
                la_primary_keys,
                la_select_all_matching: this.selectAllMatching
            })
            .then((res) => {
                let data = res.data.data;

                if(data.redirect) {
                    this.loading = false;
                    window.location.href = data.data.redirect;
                }

                if(data.download) {
                    this.loading = false;
                    const tempLink = document.createElement('a');

                    tempLink.style.display = 'none';
                    tempLink.href = data.download.url;
                    tempLink.setAttribute('download', data.download.filename);

                    if (typeof tempLink.download === 'undefined') {
                        tempLink.setAttribute('target', '_blank');
                    }

                    document.body.appendChild(tempLink);
                    tempLink.click();

                    document.body.removeChild(tempLink);

                    return;
                }

                this.$dialog.confirm({
                    message: data.message,
                    confirmText: 'OK',
                    type: 'is-' + data.type,
                    canCancel: false,
                    hasIcon: true,
                    onConfirm: () => this.fetchData()
                })
            })
            .catch(({response}) => {
                this.loading = false;

                this.$dialog.confirm({
                    message: response.data.data.message,
                    confirmText: 'OK',
                    type: 'is-danger',
                    canCancel: false,
                    hasIcon: true,
                })
            });
        },

        onTableCheckChanged() {
            if(! this.selectAllMatching) {
                return;
            }

            this.selectAllMatching = false;
        },

        onSelectAllMatchingChanged() {
            this.$nextTick(() => {
                if(this.selectAllMatching) {
                    this.checkedRows = this.data.data;
                } else {
                    this.checkedRows = [];
                }
            })
        },

        fetchData() {
            this.loading = true;

            LaHttp.get(`/${this.typeSlug}/query`, this.query)
                .then(res => {
                    this.data = res.data.data;
                    this.loading = false;

                    this.onSelectAllMatchingChanged();
                })
                .catch(err => {
                    this.alertServerError(err);

                    this.login = false;
                });

        },

        onPageChange(page) {
            this.query.page = page;

            this.pushState(this.query);
        },

        onSort(key, order) {
            this.query.order_by = key;
            this.query.order = order;

            this.pushState(this.query);
        },

        onSearch() {
            if(this.searchClock) {
                return;
            }

            this.searchClock = setTimeout(function() {
                this.query.search = this.search;
                this.pushState(this.query);
                this.searchClock = null;
            }.bind(this), 300);
        },

        onFilter() {
            this.$set(this.query, 'page', 1);
            this.pushState(this.query);
        },

        afterDelete(res, typeName, many = false) {
            if(many) {
                this.checkedRows = [];
            }

            this.$toast.open(
                (many && res.data.deleted ? res.data.deleted + ' ' : '') +
                `${typeName} deleted!`
            );

            this.pushState(this.query);
        },

        afterRestore(res, typeName, many = false) {
            if(many) {
                this.checkedRows = [];
            }

            this.$toast.open(
                (many && res.data.restored ? res.data.restored + ' ' : '') +
                `${typeName} restored!`
            );

            this.pushState(this.query);
        },

        fetchFilterData(filter) {
            if(this.controls.filters[filter]['loaded']) {
                return;
            }

            this.$set(this.controls.filters[filter], 'loading', true);

            LaHttp.get(`filters/${this.typeSlug}/${filter}`)
                .then(({data}) => {
                    this.$set(this.controls.filters[filter], 'data', data.data);
                    this.$set(this.controls.filters[filter], 'loading', false);
                    this.$set(this.controls.filters[filter], 'loaded', true);
                })
                .catch(err => {
                    this.alertServerError(err);
                    this.$set(this.controls.filters[filter], 'loading', false);
                });
        }

    },

    components: {
        LaTable,
        LaTableColumn,
        LaFieldRenderer,
        ...Filters
    }

}
