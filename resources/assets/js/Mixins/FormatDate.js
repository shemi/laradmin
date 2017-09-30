export default {

    methods: {
        formatDate(value) {
            if(Object.isObject(value) && value.date) {
                value = value.date;
            }

            return new Date(value).toLocaleString();
        }
    },

}