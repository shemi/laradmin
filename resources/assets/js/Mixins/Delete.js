import LaHttp from '../Forms/LaHttp';
import {map} from "lodash";

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
            let length = items.length;

            if(items.length <= 0) {
                return;
            }

            if(this.selectAllMatching) {
                length = this.data.total;
            }

            this.$dialog.confirm({
                title: `Deleting ${length} ${typeName}`,
                message: `Are you sure you want to <strong>delete</strong> ${length} ${typeName}? This action cannot be undone.`,
                confirmText: `Delete ${items.length} ${typeName}`,
                type: 'is-danger',
                hasIcon: true,
                onConfirm: () => {
                    this.deleteItems(items, uri, typeName, primaryKey);
                }
            })
        },

        deleteItems(items, uri, typeName, primaryKey) {
            const la_primary_keys = map(this.checkedRows, primaryKey);
            let query = this.query || {};

            LaHttp.client().post(uri, {
                ...query,
                la_primary_keys,
                la_select_all_matching: this.selectAllMatching
            })
            .then(({data}) => {
                this.afterDelete(data, typeName, true);
            })
            .catch((err) => {
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