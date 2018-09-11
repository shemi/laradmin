import native_qs from 'querystring-browser';
import _ from 'lodash';

class QueryString {

    parse(query) {
        if(! query) {
            return {};
        }

        if(typeof query === 'string') {
            if(! query.match(/\[\w*\]/)) {
                return native_qs.parse(query);
            }

            query = native_qs.parse(query);
        }

        return this.parseQueryObject(query);
    }

    parseQueryObject(query) {
        let dirty = false,
            matches;

        for(let key in query ) {
            if(! query.hasOwnProperty(key)) {
                continue;
            }

            let value = query[key];

            if(typeof value == 'string' && ! isNaN(1*value)) {
                value = 1 * value;
            }

            if(matches = key.match(/^(.+)(\[[^\]]*\])$/) ){
                let parent = matches[1];
                let child = matches[2];

                if(child.match(/^\[\s*\]$/) ) {
                    if(value instanceof Array) {
                        query[parent] = value;
                    } else {
                        query[parent] = [value];
                    }
                } else {
                    if(! (query[parent] instanceof Object)) {
                        query[parent] = {};
                    }

                    child = child.replace(/[\[\] ]/g, "");
                    query[parent][child] = value
                }

                delete query[key];

                dirty = true;
            }
        }

        return dirty ? this.parse(query) : query;
    }

    stringify(obj) {

        if (! _.isObject(obj) && _.isArray(obj)) {
            throw new Error('Invalid Object Format');
        }

        let result = [];

        _.each(obj, (val, key) => {
            if (_.isArray(val)) {
                result.push(this.stringifyArray(key, val));
            }

            else if(_.isObject(val)) {
                result.push(this.stringifyObject(key, val));
            }

            else if(! this.isEmpty(val)) {
                result.push(`${key}=${native_qs.escape(val)}`);
            }
        });

        result = result.filter(function(e){return e});

        return result.join('&');
    }

    stringifyObject(key, obj) {
        let result = [];

        _.each(obj, (val, key2) => {
            key2 = key+'['+key2+']';
            if (_.isArray(val)) {
                result.push(this.stringifyArray(key2, val));
            }

            else if(_.isObject(val)) {
                result.push(this.stringifyObject(key2, val));
            }

            else if(! this.isEmpty(val)) {
                result.push(`${key2}=${val}`);
            }
        });

        result = result.filter(function(e){return e});

        return result.join('&');
    }

    stringifyArray(key, obj) {
        let result = [];

        _.each(obj, (val, index) => {
            if (_.isObject(val)) {
                val = this.stringifyObject(`${key}[${index}]`, val);

                result.push(val);
            }

            else if (! this.isEmpty(val)) {
                result.push(`${key}[]=${val}`);
            }
        });

        return result.join('&');
    }

    isEmpty(val) {
        return val === null || val === '';
    }

}

export default new QueryString();