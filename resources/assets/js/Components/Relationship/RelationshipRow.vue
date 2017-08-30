<template>

    <div>

    </div>

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
            }
        },

        created() {
            if (! this.$parent.$data._isRelationship) {
                this.$destroy();
                throw new Error('You should wrap laRepeaterRow on a laRepeater');
            }
        }

    }
</script>