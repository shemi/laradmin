import LaHttp from '../../Forms/LaHttp';
import _ from 'lodash';

export default {

    props: {
        filter: {
            type: String,
            required: true
        },

        label: {
            type: String,
            required: true
        },

        type: {
            type: String,
            required: true
        },

        filterData: {
            type: Object
        },

        value: {
            type: [Array, String, Object],
            required: false,
            default: null
        },

    },

    watch: {
        value() {
            this.newValue = this.transformValue(this.value);
        }
    },

    data() {
        return {
            search: '',
            newValue: this.transformValue(this.value),
            loading: false,
            loaded: this.filterData.loaded,
            options: this.filterData.options || []
        }
    },

    methods: {

        transformValue(value) {
            return value;
        },

        onChange() {
            this.$emit('input', this.newValue);
        },

        fetch() {
            if(this.loaded) {
                return;
            }

            this.loading = true;

            LaHttp.get(`filters/${this.type}/${this.filter}`)
                .then(({data}) => {
                    this.options = data.data;
                    this.loading = false;
                    this.loaded = true;
                })
                .catch(err => {
                    this.alertServerError(err);
                    this.loading = false;
                });
        }

    },

    computed: {
        filteredOptions() {
            if(! this.search) {
                return this.options;
            }

            return this.options.filter((option) => {
                return option.label
                    .toString()
                    .toLowerCase()
                    .indexOf(this.search.toLowerCase()) >= 0
            });
        }
    }

}