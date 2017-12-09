export default {

    data() {
        return {
            field: {}
        }
    },

    mounted() {
        let parent = this.$parent,
            isField = parent.isField,
            index = 0;

        while (parent && ! isField && index < 10) {
            parent = parent.$parent;

            if(parent) {
                isField = parent.isField;
            } else  {
                isField = false;
            }

            index++;
        }

        if(! parent || ! isField) {
            return;
        }

        this.field = parent.newValue;
    }

}