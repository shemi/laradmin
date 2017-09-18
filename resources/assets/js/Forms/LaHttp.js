
class LaHttp {

    constructor() {
        this.axios = window.axios;
        this.apiBaseUri = window.laradmin.api_base.replace(/\/$/g, '');
    }

    uri(path) {
        if(/^(?:\w+:)?\/\/([^\s\.]+\.\S{2}|localhost[\:?\d]*)\S*$/.test(path)) {
            return path;
        }

        path = path.replace(/^\/|\/$/g, '');

        return `${this.apiBaseUri}/${path}`;
    }

    /**
     * Helper method for making GET HTTP requests.
     */
    get(uri, params) {
        return this.axios.get(this.uri(uri), {params});
    }


    /**
     * Helper method for making POST HTTP requests.
     */
    post(uri, form) {
        return this.sendForm('post', uri, form);
    }


    /**
     * Helper method for making PUT HTTP requests.
     */
    put(uri, form) {
        return this.sendForm('put', uri, form);
    }


    /**
     * Helper method for making PATCH HTTP requests.
     */
    patch(uri, form) {
        return this.sendForm('patch', uri, form);
    }


    /**
     * Helper method for making DELETE HTTP requests.
     */
    delete(uri, form) {
        return this.sendForm('delete', uri, form);
    }

    /**
     * Helper method for making DELETE HTTP requests.
     */
    planeDelete(uri, params) {
        return this.axios.delete(this.uri(uri), {params});
    }

    /**
     * Send the form to the back-end server.
     *
     * This function will clear old errors, update "busy" status, etc.
     */
    sendForm(method, uri, form) {
        let self = this;

        return new Promise((resolve, reject) => {
            form.startProcessing();

            console.log(self.uri(uri));

            this.axios[method](self.uri(uri), form.toJson())
                .then(response => {
                    form.finishProcessing();

                    resolve(response.data);
                })
                .catch((error) => {
                    let errors = error.response.data;

                    if(errors.errors) {
                        errors  = errors.errors;
                    }

                    form.errors.set(errors);
                    form.busy = false;

                    reject(error.response);
                });
        });
    }

}

export default new LaHttp();