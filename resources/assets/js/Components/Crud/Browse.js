import MixinsLoader from '../../Helpers/MixinsLoader';
import FormatDate from '../../Mixins/FormatDate';
import LaHttp from '../../Forms/LaHttp';
import {LaTable, LaTableColumn} from '../Table';
import deleteMixin from '../../Mixins/Delete';
import ServerError from '../../Mixins/ServerError';

export default {
    mixins: MixinsLoader.load(
        'crudBrowse',
        [FormatDate, deleteMixin, ServerError]
    ),

    props: ['typeName', 'typeSlug'],

    data() {
        return {
            loading: true,
            checkedRows: [],
            selected: {},
            search: "",
            searchClock: null,
            query: {
                order_by: null,
                order: null,
                page: 1,
                search: ""
            },
            data: {
                data: []
            }
        }
    },

    created() {
        this.fetchData();
    },

    watch: {

    },

    methods: {

        fetchData() {
            this.loading = true;

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
            this.fetchData();
        },

        onSort(key, order) {
            this.query.order_by = key;
            this.query.order = order;
            this.fetchData();
        },

        onSearch() {
            if(this.searchClock) {
                return;
            }

            this.searchClock = setTimeout(function() {
                this.query.search = this.search;
                this.fetchData();
                this.searchClock = null;
            }.bind(this), 300);
        },

        afterDelete(res, typeName, many = false) {
            if(many) {
                this.checkedRows = [];
            }

            this.$toast.open(
                (many && res.data.deleted ? res.data.deleted + ' ' : '') +
                `${typeName} deleted!`
            );

            this.fetchData();
        }

    },

    components: {
        LaTable,
        LaTableColumn
    }

}