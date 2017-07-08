export default {

    props: ['menus'],

    data() {
        return {
            'loading': false,
            checkedRows: [],
            selected: {},
        }
    },

    methods: {
        formatDate(value, row) {
            return new Date(value).toLocaleString();
        }
    },

}