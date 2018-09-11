import queryString from '../Plugins/query-string-object';

export default {

    methods: {
        /**
         * Initialize push state handling for tabs.
         */
        usePushState() {
            this.broadcastHashChange();

            window.addEventListener('hashchange', e => {
                this.broadcastHashChange();
            });
        },


        isNumeric(n) {
            return !isNaN(parseFloat(n)) && isFinite(n);
        },

        /**
         * Activate the tab for the current hash in the URL.
         */
        broadcastHashChange() {
            let hash = window.location.hash.substring(2);
            let parameters = hash.split('/');
            let query = hash.split('?');
            query = query.pop();
            hash = parameters.shift();

            if(query) {
                query = queryString.parse(query);

                for(let key of Object.keys(query)) {
                    let value = query[key];

                    if(this.isNumeric(value)) {
                        value = parseInt(value);
                    }

                    this.$set(this.query, key, value);
                }
            }

            this.fetchData();
        },

        pushState(params = {}) {
            let query = queryString.stringify(params);

            window.history.pushState(null, null, '#?'+query);

            this.broadcastHashChange();
        }

    }
};