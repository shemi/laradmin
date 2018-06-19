import { replace } from 'lodash';

export default {

    value(value, defaultValue = null) {
        if (value) {
            return value;
        }

        return typeof defaultValue === 'function' ?
            defaultValue(value) :
            defaultValue;
    },

    slugify(value = "") {
        let from = "ãàáäâẽèéëêìíïîõòóöôùúüûñç·/_,:;",
            to = "aaaaaeeeeeiiiiooooouuuunc------",
            i,
            l = from.length;

        value = value.replace(/^\s+|\s+$/g, '');
        value = value.toLowerCase();

        for (i = 0; i < l; i++) {
            value = value.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
        }

        return value
            .replace(/[^a-z0-9 -]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
    },

    makeId(length = 5) {
        let id = "",
            possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789",
            i;

        for (i = 0; i < length; i++) {
            id += possible.charAt(Math.floor(Math.random() * possible.length));
        }

        return id;
    },

    capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

};