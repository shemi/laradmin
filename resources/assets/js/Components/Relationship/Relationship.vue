<template>

    <div class="fa-relationship">



    </div>

</template>

<script>
    import Helpers from '../../Helpers/Helpers';
    import LaHttp from '../../Forms/LaHttp';

    export default {

        props: {
            value: {
                type: Array,
                required: true
            },
            form: {
                type: Object,
                required: true
            },
            label: {
                type: String,
                default: "Items"
            },
            labelSingular: {
                type: String,
                default: "Item"
            },
            addButtonText: {
                type: String,
                default: "Add Row"
            }
        },

        data() {
            return {
                _isRelationship: true,
                columns: []
            }
        },

        created() {

        },

        methods: {

        },

        computed: {
            rows() {
                return this.value;
            }
        }

    }
</script>