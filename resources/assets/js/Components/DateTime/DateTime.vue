<template>
    <div class="control"
         :class="rootClasses">
        <flat-pickr
                v-if="ready"
                v-model="newValue"
                :config="newConfig"
                class="input"
                :class="inputClasses"
                :placeholder="placeholder"
                @on-change="updateInput"
                :name="formKey">
        </flat-pickr>

        <b-icon v-if="icon"
                class="is-left"
                :icon="icon"
                :pack="iconPack"
                :size="iconSize">
        </b-icon>
    </div>
</template>

<script>
    import flatPickr from 'vue-flatpickr-component';
    import l10n from 'flatpickr/dist/l10n/index.js';
    import moment from 'moment-timezone';
    import { cloneDeep, isArray } from 'lodash';

    export default {
        name: 'la-date-time',

        props: {
            alignment: String,
            value: {
                type: [Object, String, Date]
            },
            size: String,
            expanded: Boolean,
            rounded: Boolean,
            icon: String,
            iconPack: String,
            formKey: String,
            type: String,
            iconSize: String,

            placeholder: {
                type: String,
                default: 'Pick date'
            },

            timezone: {
                type: String,
                default: 'local'
            },

            config: {
                type: Object,
                default: () => ({})
            }
        },

        data() {
            return {
                ready: false,
                newValue: null,
                newConfig: {}
            }
        },

        mounted() {
            this.setValue(this.value);
            this.setConfig();
        },

        watch: {
            value(newValue, oldValue) {
                this.setValue(newValue);
            }
        },

        methods: {
            setValue(value) {
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
                    this.newValue = null;

                    return;
                }

                if(this.timezone === 'local') {
                    value.local();
                } else if(this.timezone) {
                    value.tz(this.timezone);
                }

                this.newValue = value.toISOString();
            },

            updateInput(value) {
                if(! value || (isArray(value) && value.length <= 0)) {
                    this.$emit('input', value);

                    return;
                }

                let newValue = [],
                    date;

                for(date of value) {
                    date = moment(date);

                    if(! date.isValid()) {
                        continue;
                    }

                    date.utc();

                    newValue.push(date);
                }

                if(['datetime_range'].indexOf(this.type) < 0) {
                    value = value[0];
                }

                this.$emit('input', value);
            },

            setConfig() {
                let original = cloneDeep(this.config),
                    replace = {
                        mode: "single",
                        noCalendar: this.type === 'time',
                        enableTime: this.type !== 'date',
                        inline: false,
                        dateFormat: 'Z',
                        altInput: true
                    },
                    replaceKey,
                    replaceKeys = Object.keys(replace);

                for (replaceKey of replaceKeys) {
                    original[replaceKey] = replace[replaceKey];
                }

                if(original.locale && typeof l10n[original.locale] === 'undefined') {
                    original.locale = l10n[original.locale];
                } else {
                    original.locale = l10n.default;
                }

                this.newConfig = original;
                this.ready = true;
            }
        },

        computed: {

            rootClasses() {
                return [
                    this.iconPosition,
                    this.size,
                    {
                        'is-expanded': this.expanded,
                        'is-clearfix': !this.hasMessage
                    }
                ]
            },
            inputClasses() {
                return [
                    this.statusType,
                    this.size,
                    { 'is-rounded': this.rounded }
                ]
            },
            hasIconRight() {
                return this.statusType;
            },

            iconPosition() {
                if (this.icon && this.hasIconRight) {
                    return 'has-icons-left has-icons-right';
                }

                else if (!this.icon && this.hasIconRight) {
                    return 'has-icons-right';
                }

                else if (this.icon) {
                    return 'has-icons-left';
                }
            },

            statusTypeIcon() {
                switch (this.statusType) {
                    case 'is-success': return 'check';
                    case 'is-danger': return 'alert-circle';
                    case 'is-info': return 'information';
                    case 'is-warning': return 'alert';
                }
            },

            hasMessage() {
                return !! this.statusMessage;
            }
        },

        components: {
            flatPickr
        }

    }
</script>

<style lang="stylus">
    $calendar_background = #ffffff
    $calendar_border_color = #d3d6db

    $months_color = #111
    $months_background = transparent

    $weekdays_background = transparent

    $day_text_color = #222324
    $day_hover_background_color = #d3d6db

    $today_color = #ed6c63
    $selected_day_background = #1fc8db

    @import '~flatpickr/src/style/flatpickr'

    .flatpickr-calendar.hasWeeks {
        width: auto !important
    }
</style>