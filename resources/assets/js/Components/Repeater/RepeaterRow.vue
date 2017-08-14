<template>

    <td :data-label="label">
        <slot></slot>
    </td>

</template>

<script>
    export default {

        props: {
            label: {
                type: String,
                required: true
            },
            field: {
                type: String,
                required: true
            },
            width: [Number, String],
            visible: {
                type: Boolean,
                default: true
            }
        },

        created() {
            if (! this.$parent.$data._isRepeater) {
                this.$destroy();
                throw new Error('You should wrap laRepeaterRow on a laRepeater');
            }


            // Since we're using scoped prop the columns gonna be multiplied,
            // this finds when to stop based on the key prop.
            const repeated = this.$parent.columns.some((column) => {
                return column.field === this.field
            });

            ! repeated && this.$parent.columns.push(this.$props);
        }

    }
</script>