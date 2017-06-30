import LaFormErrors from './LaFormErrors';

class LaForm {

    constructor(data) {
        if(typeof data !== 'object') {
            throw new Error('Form data must be object');
        }

        this.extend(data);
        this.errors = new LaFormErrors();
        this.busy = false;
        this.successful = false;
        this.submitted = false;
    }

    extend(data) {
        const keys = Object.keys(data);

        for (let key in keys) {
            this[key] = keys[key];
        }
    }

    startProcessing() {
        this.errors.forget();
        this.busy = true;
        this.successful = false;
        this.submitted = true;
    }

    finishProcessing() {
        this.busy = false;
        this.successful = true;
    }

    resetStatus() {
        this.errors.forget();
        this.busy = false;
        this.successful = false;
        this.submitted = false;
    }

    setErrors(errors) {
        this.busy = false;
        this.errors.set(errors);
    }

}
