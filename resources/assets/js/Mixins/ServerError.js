export default {

    methods: {
        alertServerError(err) {
            let data = err.response ? err.response.data : err,
                code = data.code ? data.code + ' ' : '';

            this.$dialog.alert({
                title: `${code}Server error`,
                message: `The server respond width status code: <b>${code}</b>.<br> message: <code>${data.message}</code>`,
                confirmText: 'OK',
                type: 'is-danger',
                hasIcon: true
            });
        }
    }

}