export default {

    methods: {
        alertServerError(err) {
            let data = err.response ? err.response.data : err;

            this.$dialog.alert({
                title: `${data.code} Server error`,
                message: `The server respond width status code: <b>${data.code}</b>.<br> message: <code>${data.message}</code>`,
                confirmText: 'OK',
                type: 'is-danger',
                hasIcon: true
            });
        }
    }

}