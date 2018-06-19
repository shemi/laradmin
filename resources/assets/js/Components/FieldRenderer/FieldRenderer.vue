<template>

    <div class="la-field-renderer">
        {{ value }}
    </div>

</template>

<script>
    import Helpers from '../../Helpers/Helpers';
    import { camelCase, upperFirst } from 'lodash';
    import moment from 'moment-timezone';
    import flatpickr from 'flatpickr';

    export default {
        name: 'la-field-renderer',

        props: {
            form: {
                type: Object,
                required: true
            },
            formKey: {
                type: String,
                required: true
            },
            type: {
                type: String,
                required: true
            },
            emptyString: {
                type: String,
                required: true
            },
            templateOptions: {
                type: Object,
                required: true
            },
            browseSettings: {
                type: Object,
                required: true
            },
        },

        mounted() {

        },

        methods: {

            transformValue(value) {
                let type = upperFirst(camelCase(this.type)),
                    transformMethod = `transform${type}Value`;

                if(typeof this[transformMethod] !== 'function') {
                    type = typeof value;
                    transformMethod = `transform${type}Value`;

                    if(typeof this[transformMethod] === 'function') {
                        return this[transformMethod](value);
                    }

                    if(typeof value.toString === 'function') {
                        return value.toString();
                    }

                    return value;
                }

                return this[transformMethod](value);
            },

            transformDatetimeValue(value) {
                if(value instanceof Date || value instanceof moment || typeof value === 'string') {
                    value = moment(value);
                }

                else if(typeof value === 'object') {
                    if(value.timezone) {
                        value = moment.tz(value.date, value.timezone).utc();
                    } else {
                        value = moment(value.date);
                    }
                }

                if(! value || ! value.isValid()) {
                    return "INVALID DATE";
                }

                if(this.timezone === 'local') {
                    value.local();
                }
                else if(this.timezone) {
                    value.tz(this.timezone);
                }

                console.log(this.dateFormat);

                return flatpickr.formatDate(value.toDate(), this.dateFormat);
            },

            transformDateValue(value) {
                return this.transformDatetimeValue(value);
            },

            transformTimeValue(value) {
                return this.transformDatetimeValue(value);
            }

        },

        computed: {
            value() {
                let value = this.form[this.formKey];

                if(! value) {
                    return this.emptyString;
                }

                return this.transformValue(value);
            },

            dateFormat() {
                if(this.browseSettings.date_format) {
                    return this.browseSettings.date_format;
                }

                if(this.templateOptions.datetime && this.templateOptions.datetime.altFormat) {
                    return this.templateOptions.datetime.altFormat;
                }

                return 'F j, Y H:i';
            },

            timezone() {
                if(this.browseSettings.timezone) {
                    return this.browseSettings.timezone;
                }

                if(this.templateOptions.datetime && this.templateOptions.datetime.timezone) {
                    return this.templateOptions.datetime.timezone;
                }

                return 'local';
            }
        }

    }

</script>