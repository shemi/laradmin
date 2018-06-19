import LaFormErrors from './LaFormErrors';
import LaHttp from './LaHttp';
import Vue from 'vue';

import File from 'vue-clip/src/File';
import Helpers from '../Helpers/Helpers';

class LaForm {

    constructor(data) {
        if(typeof data !== 'object') {
            throw new Error('Form data must be object');
        }

        this.lifetimeKeys = [];
        this.original = data;

        this.extend(data);

        this.errors = new LaFormErrors();
        this.busy = false;
        this.successful = false;
        this.submitted = false;
    }

    extend(data) {
        const keys = Object.keys(data);
        let keyIndex,
            key,
            value,
            transformMethod,
            type;

        for (keyIndex in keys) {
            key = keys[keyIndex];
            value = data[key];
            value = this.transformValue(data, key);

            Vue.set(this, key, value);

            if(this.lifetimeKeys.indexOf(key) < 0) {
                this.lifetimeKeys.push(key);
            }
        }
    }

    transformValue(data, key, typePrefix = "") {
        const types = window.laradmin.type ? window.laradmin.type.types : false;
        let value = data[key],
            type,
            transformMethod;

        if(! types || ! types[typePrefix + key]) {
            return value;
        }

        type = Helpers.capitalizeFirstLetter(types[typePrefix + key]);
        transformMethod = `transform${type}Value`;

        if(typeof this[transformMethod] === 'function') {
            value = this[transformMethod](value, typePrefix + key);
        }

        return value;
    }

    rebuild(data) {
        this.extend(data);
        this.resetStatus();
    }

    reset() {
        for (let key in this.lifetimeKeys) {
            delete this[key];
        }

        this.extend(this.original);
    }

    transformDateTimeValue(val) {
        if(val) {
            val = new Date(val);
        }

        return val;
    }

    transformDateValue(val) {
        return this.transformDateTimeValue(val);
    }

    transformTimeValue(val) {
        return this.transformDateTimeValue(val);
    }

    transformFilesValue(files) {
        if(! files || files.length <= 0) {
            return [];
        }

        return files.map(file => {
            return this.transformFileValue(file);
        });
    }

    transformImageValue(file) {
        return this.transformFileValue(file);
    }

    transformRepeaterValue(value, parentKey) {
        let rowIndex,
            newValue = [],
            keys,
            keyIndex;

        if(! value || value.length === 0) {
            return [];
        }

        for (rowIndex in value) {
            let row = {};
            keys = Object.keys(value[rowIndex]);

            for (keyIndex in keys) {
                let key = keys[keyIndex];

                row[key] = this.transformValue(value[rowIndex], key, parentKey + '.');
            }

            newValue[rowIndex] = row;
        }

        return newValue;
    }

    transformGroupValue(value, parentKey) {
        if(Array.isArray(value)) {
            return this.transformGroupValue(value[0]);
        }

        if(typeof value !== 'object') {
            return {};
        }

        let newObject = {};
        let keys = Object.keys(value);

        for(let key of keys) {
            newObject[key] = this.transformValue(value, key, parentKey + '.');
        }

        return newObject;
    }

    transformFileValue(file) {
        if(! file || typeof file === File) {
            return file;
        }

        let fileModel = new File({
            status: file.id ? 'exists' : file.status,
            name: file.name,
            upload: {},
            type: '',
            size: file.size
        });

        fileModel.customAttributes = {
            alt: file.alt,
            caption: file.caption,
            ext: file.ext,
            uri: file.uri,
            id: file.id
        };

        return fileModel;
    }

    toJson() {
        const keys = Object.keys(this.original);
        let keyIndex,
            key,
            toJson = {};

        for (keyIndex in keys) {
            key = keys[keyIndex];
            toJson[key] = this[key];
        }

        return toJson;
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
