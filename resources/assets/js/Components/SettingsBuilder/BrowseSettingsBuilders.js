import LaHttp from '../../Forms/LaHttp';

export default {

    name: 'browse-settings-builders',

    data() {
        return {
            loading: false,
            search: "",
            selected: {},
            settings: []
        }
    },

    mounted() {
        this.fetchData();
    },

    methods: {
        fetchData() {
            this.loading = true;

            LaHttp.get(`/settings-builder/query`)
                .then(res => {
                    this.types = [];
                    res.data.data.settings.forEach((item) => this.settings.push(item));
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
        filteredSettings() {
            return this.settings;
        }
    }

}