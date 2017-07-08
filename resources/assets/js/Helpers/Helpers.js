export default {

    value(value, defaultValue = null) {
        if(value) {
            return value;
        }

        return typeof defaultValue === 'function' ?
            defaultValue(value) :
            defaultValue;
    }

};