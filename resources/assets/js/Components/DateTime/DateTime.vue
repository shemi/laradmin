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
                @input="$emit('input', this.newValue)"
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

    export default {
        props: {
            alignment: String,
            value: {
                type: [Object, String]
            },
            size: String,
            expanded: Boolean,
            rounded: Boolean,
            icon: String,
            iconPack: String,
            formKey: String,
            type: String,

            placeholder: {
                type: String,
                default: 'Pick date'
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
            value(newValue) {
                this.setValue(newValue);
            }
        },

        methods: {
            setValue(value) {
                if(value instanceof Date) {
                    this.newValue = value.toString();

                    return;
                }

                this.newValue = value;
            },

            setConfig() {
                let replace = {
                    mode: "single",
                    noCalendar: false,
                    enableTime: true,
                    inline: false,
                }
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