import ParentForm from './ParentForm';
import LaFieldsList from '../Components/Types/FieldsList';

export default {

    props: {
        options: Array,
        formKey: String,
        value: Object
    },

    mixins: [ParentForm],

    data() {
        return {
            panel: this.value,
            classList: [
                this.$options._componentTag
            ],
        }
    },

    watch: {
        value(newValue) {
            this.panel = newValue;
        }
    },

    methods: {
        input(value, fieldKey = null) {
            if(fieldKey) {
                this.$set(this.panel, fieldKey, value);
            } else {
                this.panel = value;
            }

            this.$emit('input', this.panel);
        },

        fieldKey(key) {
            return this.formKey + '.' + key;
        },

        hasErrors(key) {
            return this.form.errors.has(this.fieldKey(key));
        },

        errorMessage(key) {
            if(! this.hasErrors(key)) {
                return null;
            }

            return this.form.errors.get(this.fieldKey(key));
        }

    },

    components: {
        LaFieldsList
    }

}