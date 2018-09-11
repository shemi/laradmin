import MixinsLoader from '../../Helpers/MixinsLoader';
import FormatDate from '../../Mixins/FormatDate';
import LaHttp from '../../Forms/LaHttp';
import {LaTable, LaTableColumn} from '../Table';
import deleteMixin from '../../Mixins/Delete';
import ServerError from '../../Mixins/ServerError';
import SimpleRouter from '../../Mixins/SimpleRouter';
import LaFieldRenderer from  '../FieldRenderer/FieldRenderer';
import Filters from '../Filters/index';

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
            query: {
                order_by: null,
                order: null,
                filters: {},
                page: 1,
                search: ""
            },
            data: {
                data: []
            }
        }
    },

    mounted() {
        this.usePushState();
    },

    created() {
        for(let key of Object.keys(this.controls.filters)) {
            this.$set(this.query.filters, key, null);
        }
    },

    watch: {

    },

    methods: {

        fetchData() {
            this.loading = true;
            //
            // var stack = new Error().stack;
            // console.log("PRINTING CALL STACK");
            // console.log( stack );

            LaHttp.get(`/${this.typeSlug}/query`, this.query)
                .then(res => {
                    this.data = res.data.data;
                    this.loading = false;
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