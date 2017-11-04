import LaForm from '../Forms/LaForm';

export default {

    data() {
        return {
            form: new LaForm({})
        }
    },

    mounted() {
        let parent = this.$parent,
            form = parent.form,
            index = 0;

        while (! form && index < 10) {
            parent = parent.$parent;
            form = parent ? parent.form : null;
            index++;
        }

        if(! form) {
            throw new Error("Parent most have form object");
        }

        this.form = form;
    }

}