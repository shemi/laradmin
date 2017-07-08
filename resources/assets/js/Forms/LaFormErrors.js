import Vue from 'vue';

class LaFormErrors {

    constructor() {
        this.errors = {};
    }

    hasErrors() {
        return Object.keys(this.errors).length > 0;
    }

    has(field) {
        return Object.keys(this.errors).indexOf(field) > -1;
    }

    all() {
        return this.errors;
    }

    get(field) {
        if(this.has(field)) {
            return this.errors[field][0];
        }
    }

    set(errors) {
        if (typeof errors === 'object') {
            this.errors = errors;
        } else {
            this.errors = {'form': ['Something went wrong. Please try again.']};
        }
    }

    forget(field) {
        if (typeof field === 'undefined') {
            this.errors = {};
        } else {
            Vue.delete(this.errors, field);
        }
    }

}

export default LaFormErrors;
