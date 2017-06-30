
class LaHttp {

    constructor() {
        this.axios = window.axios;
        this.apiBaseUri = window.laradmin.api_base.replace(/^\/|\/$/g, '');
    }

    uri(path) {
        path = path.replace(/^\/|\/$/g, '');

        if(/^(?:\w+:)?\/\/([^\s\.]+\.\S{2}|localhost[\:?\d]*)\S*$/.test(path)) {
            return path;
        }

        return `${this.apiBaseUri}/${path}`;
    }

    /**
     * Helper method for making GET HTTP requests.
     */
    get(uri, params) {
        return this.axios('post', this.uri(uri), form);
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
     * Send the form to the back-end server.
     *
     * This function will clear old errors, update "busy" status, etc.
     */
    sendForm(method, uri, form) {
        return new Promise((resolve, reject) => {
            form.startProcessing();

            this.axios[method](this.uri(uri), JSON.parse(JSON.stringify(form)))
                .then(response => {
                    form.finishProcessing();

                    resolve(response.data);
                })
                .catch((error) => {
                    form.errors.set(error.response.data);
                    form.busy = false;

                    reject(error.response.data);
                });
        });
    }

}

export default new LaHttp();