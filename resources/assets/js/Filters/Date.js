import Vue from 'vue';
import moment from 'moment';

Vue.filter('date', function(value, format = null) {
    let date = value;

    if(! date) {
        return "";
    }

    if(value.date) {
        if(value.timezone && value.timezone.toLowerCase() === 'utc') {
            date = moment.utc(value.date);
            date.local();
        } else {
            date = moment(value.date);
        }
    } else {
        date = moment(value);
    }

    if(! date.isValid()) {
        return value;
    }

    return date.format(format || 'DD/MM/YYYY HH:MM');
});