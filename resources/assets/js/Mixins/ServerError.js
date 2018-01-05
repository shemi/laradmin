export default {

    methods: {
        alertServerError(err) {
            let data = err.response ? err.response.data : err.data,
                code = data.code ? data.code + ' ' : '',
                resultCode = data.resultCode ? `<small>(${data.resultCode})</small>` : '';

            this.$dialog.alert({
                title: `${code}Server error`,
                message: `The server respond width
                          <br>
                          <b>status code:</b><code>${code}${resultCode}</code>.
                          <br>
                          <b>message:</b><br>
                          <code>${data.message}</code>`,
                confirmText: 'OK',
                type: 'is-danger',
                hasIcon: true
            });
        }
    }

}