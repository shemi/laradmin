import LaHttp from '../Forms/LaHttp';

export default {

    methods: {
        onDelete(uri, typeName) {

            this.$dialog.confirm({
                title: `Deleting ${typeName}`,
                message: `Are you sure you want to <strong>delete</strong> this ${typeName}? This action cannot be undone.`,
                confirmText: `Delete ${typeName}`,
                type: 'is-danger',
                hasIcon: true,
                onConfirm: () => {
                    this.deleteItem(uri, typeName);
                }
            })

        },

        deleteItem(uri, typeName) {
            LaHttp.planeDelete(uri)
                .then((res) => {
                    this.afterDelete(res, typeName);
                }).catch((err) => {
                    let data = err.response ? err.response.data : err;

                    this.$dialog.alert({
                        title: `Action Canceled.`,
                        message: `The server respond width status code: <b>${data.code}</b>.
                                  <br> message: <code>${data.message}</code>`,
                        confirmText: 'OK',
                        type: 'is-danger',
                        hasIcon: true
                    });
                });
        },

        afterDelete(res, typeName) {
            let redirect = res.data.data.redirect;

            this.$toast.open(
                `${typeName} deleted` + (redirect ? ', redirecting...' : '!')
            );

            if(redirect) {
                window.location.href = redirect;
            }
        }
    }

}