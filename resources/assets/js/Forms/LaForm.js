import LaFormErrors from './LaFormErrors';
import LaHttp from './LaHttp';

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
        let keyIndex,
            key;

        for (keyIndex in keys) {
            key = keys[keyIndex];
            this[key] = data[key];
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

    post(uri) {
        return LaHttp.post(uri, this);
    }

    put(uri) {
        return LaHttp.put(uri, this);
    }

    path(uri) {
        return LaHttp.patch(uri, this);
    }

    delete(uri) {
        return LaHttp.delete(uri, this);
    }
}

export default LaForm;
