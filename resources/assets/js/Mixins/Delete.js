import LaHttp from '../Forms/LaHttp';
import {map} from "lodash";

export default {

    methods: {
        onDelete(uri, typeName) {
            let message;

            if(this.isTrash) {
                message = `You are about to <strong>permanently delete</strong> this trashed ${typeName}? This action cannot be undone.`;
            } else {
                message = `Are you sure you want to <strong>delete</strong> this ${typeName}? This action cannot be undone.`;
            }

            this.$dialog.confirm({
                title: this.isTrash ? `Permanently ${typeName}` : `Deleting ${typeName}`,
                message: message,
                confirmText: `Delete ${typeName}`,
                type: 'is-danger',
                hasIcon: true,
                onConfirm: () => {
                    this.deleteItem(uri, typeName);
                }
            })

        },

        onRestore(uri, typeName) {
            this.$dialog.confirm({
                title: `Restoring ${typeName}`,
                message: `Are you sure you want to <strong>Restore</strong> this ${typeName}?`,
                confirmText: `Restore ${typeName}`,
                type: 'is-info',
                hasIcon: true,
                onConfirm: () => {
                    this.restoreItem(uri, typeName);
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

            let message;

            if(this.isTrash) {
                message = `Are you sure you want to <strong>permanently delete</strong> those ${length} trashed ${typeName}? This action cannot be undone.`;
            } else {
                message = `Are you sure you want to <strong>delete</strong> ${length} ${typeName}? This action cannot be undone.`;
            }

            this.$dialog.confirm({
                title: this.isTrash ? `Permanently Deleting ${length} ${typeName}` : `Deleting ${length} ${typeName}`,
                message: message,
                confirmText: `Delete ${items.length} ${typeName}`,
                type: 'is-danger',
                hasIcon: true,
                onConfirm: () => {
                    this.deleteItems(items, uri, typeName, primaryKey);
                }
            })
        },

        onRestoreSelected(items, uri, typeName, primaryKey) {
            let length = items.length;

            if(items.length <= 0) {
                return;
            }

            if(this.selectAllMatching) {
                length = this.data.total;
            }

            this.$dialog.confirm({
                title: `Restoring ${length} ${typeName}`,
                message: `Are you sure you want to <strong>restore</strong> ${length} ${typeName}?`,
                confirmText: `Restoring ${items.length} ${typeName}`,
                type: 'is-info',
                hasIcon: true,
                onConfirm: () => {
                    this.restoreItems(items, uri, typeName, primaryKey);
                }
            })
        },

        restoreItems(items, uri, typeName, primaryKey) {
            const la_primary_keys = map(this.checkedRows, primaryKey);
            let query = this.query || {};

            LaHttp.client().post(uri, {
                ...query,
                la_primary_keys,
                la_select_all_matching: this.selectAllMatching,
                trash: this.isTrash,
            })
                .then(({data}) => {
                    this.afterRestore(data, typeName, true);
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

        deleteItems(items, uri, typeName, primaryKey) {
            const la_primary_keys = map(this.checkedRows, primaryKey);
            let query = this.query || {};

            LaHttp.client().post(uri, {
                ...query,
                la_primary_keys,
                la_select_all_matching: this.selectAllMatching,
                trash: this.isTrash,
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

        restoreItem(uri, typeName) {
            LaHttp.planePost(uri)
                .then(({data}) => {
                    this.afterRestore(data, typeName);
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
        },

        afterRestore(res, typeName, many = false) {
            let redirect = res.data.redirect;

            this.$toast.open(
                (many && res.data.restored ? res.data.restored + ' ' : '') +
                `${typeName} restored` + (redirect ? ', redirecting...' : '!')
            );

            if(redirect) {
                this.$nextTick(() => {
                    window.location.href = redirect;
                });
            }
        }
    }

}
