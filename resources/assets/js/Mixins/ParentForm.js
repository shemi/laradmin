import LaForm from '../Forms/LaForm';

export default {

    data() {
        return {
            form: new LaForm({})
        }
    },

    mounted() {
        let form = this.$parent.form || this.$parent.$parent.form;

        if(! form) {
            throw new Error("Parent most have form object");
        }

        this.form = form;
    }

}