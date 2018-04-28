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

        onDeleteSelected(items, uri, typeName, primaryKey) {
            if(items.length <= 0) {
                return;
            }

            this.$dialog.confirm({
                title: `Deleting ${items.length} ${typeName}`,
                message: `Are you sure you want to <strong>delete</strong> ${items.length} ${typeName}? This action cannot be undone.`,
                confirmText: `Delete ${items.length} ${typeName}`,
                type: 'is-danger',
                hasIcon: true,
                onConfirm: () => {
                    this.deleteItems(items, uri, typeName, primaryKey);
                }
            })
        },

        deleteItems(items, uri, typeName, primaryKey) {
            const ids = [];
            let item;

            if(typeof items[0] === 'object') {
                for(item of items) {
                    ids.push(item[primaryKey || 'id']);
                }
            }

            LaHttp.planeDelete(uri, {ids})
                .then(({data}) => {
                    this.afterDelete(data, typeName, true);
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

        deleteItem(uri, typeName) {
            LaHttp.planeDelete(uri)
                .then(({data}) => {
                    this.afterDelete(data, typeName);
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

        afterDelete(res, typeName, many = false) {
            let redirect = res.data.redirect;

            this.$toast.open(
                (many && res.data.deleted ? res.data.deleted + ' ' : '') +
                `${typeName} deleted` + (redirect ? ', redirecting...' : '!')
            );

            if(redirect) {
                this.$nextTick(() => {
                    window.location.href = redirect;
                });
            }
        }
    }

}