import LaHttp from '../../Forms/LaHttp';

export default {

    name: 'BrowseTypes',

    data() {
        return {
            loading: false,
            search: "",
            selected: {},
            types: []
        }
    },

    mounted() {
        this.fetchData();
    },

    methods: {
        fetchData() {
            this.loading = true;

            LaHttp.get(`/types/query`)
                .then(res => {
                    this.types = [];
                    res.data.data.types.forEach((item) => this.types.push(item));
                    this.loading = false;

                    this.$nextTick(function() {
                        this.$refs.table.initSort();
                    });
                })
                .catch(err => {
                    this.alertServerError(err);

                    this.login = false;
                });

        }
    },

    computed: {
        filteredTypes() {
            return this.types;
        }
    }

}