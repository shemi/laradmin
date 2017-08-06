
export default {

    name: 'BrowseTypes',

    props: ['types'],

    data() {
        return {
            loading: false,
            checkedRows: [],
            selected: {},
        }
    },

    mounted() {

    },

    methods: {
        formatDate(value, row) {
            return new Date(value).toLocaleString();
        }
    },

}