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
                    this.$toast.open({
                        message: 'Whoops.. Something went wrong!',
                        type: 'is-danger'
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