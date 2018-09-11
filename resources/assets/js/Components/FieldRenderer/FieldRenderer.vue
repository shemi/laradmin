<template>

    <div class="la-field-renderer" :class="['value-type-'+type]">
        <div v-if="displayType === 'string'"
             :title="transformedValue">
            {{ transformedValue | truncate(100) }}
        </div>

        <div v-if="displayType === 'tags'">
            <b-taglist>
                <b-tag v-for="(tag, index) in transformedValue"
                       :title="tag"
                       :key="index">
                    {{ tag | truncate(50) }}
                </b-tag>
                <a class="tag is-link is-info"
                   v-if="hasMore"
                   @click.prevent="toggleShowMore">
                    {{ showingMore ? 'Less' : (fullValue.length - maxArrayLength) + ' More' }}
                </a>
            </b-taglist>
        </div>

        <div v-if="displayType === 'list'">
            <ul class="list">
                <li v-for="(item, index) in transformedValue"
                    :key="index">
                    {{ item | truncate(100) }}
                </li>
                <li class="more-link" v-if="hasMore">
                    <a class="is-link is-info"
                       @click.prevent="toggleShowMore">
                        {{ showingMore ? 'Less' : (fullValue.length - maxArrayLength) + ' More' }}
                    </a>
                </li>
            </ul>
        </div>

        <div v-if="displayType === 'image'" class="image">
            <p class="image is-48x48" v-if="transformedValue[0]">
                <img :src="transformedValue[0].uri" :alt="transformedValue[0].alt">
            </p>
        </div>

        <div v-if="displayType === 'images'" class="images">
            <p class="image is-48x48"
               v-for="(image, index) in transformedValue"
               :key="index">
                <img :src="image.uri" :alt="image.alt">
            </p>
        </div>

        <div v-if="displayType === 'files'" class="files">
            <ul class="list">
                <li v-for="(file, index) in transformedValue"
                    :key="index">
                    <a :href="file.uri" :title="file.name" target="_blank">
                        <b-icon icon="paperclip"></b-icon>
                        <span>{{ file.name | truncate(35) }}</span>
                    </a>
                </li>
                <li class="more-link" v-if="hasMore">
                    <a class="is-link is-info"
                       @click.prevent="toggleShowMore">
                        {{ showingMore ? 'Less' : (fullValue.length - maxArrayLength) + ' More' }}
                    </a>
                </li>
            </ul>
        </div>

    </div>

</template>

<script>
    import { camelCase, upperFirst, isArray, compact, take, takeRight } from 'lodash';
    import moment from 'moment-timezone';
    import flatpickr from 'flatpickr';
    import MixinsLoader from '../../Helpers/MixinsLoader';
    import DefaultImage from '../../Mixins/DefaultImage';

    export default {
        name: 'la-field-renderer',

        mixins: MixinsLoader.load(
            'fieldRenderer',
            [DefaultImage]
        ),

        props: {
            value: {
                required: true
            },
            formKey: {
                type: String,
                required: true
            },
            type: {
                type: String,
                required: true,
            },
            emptyString: {
                type: String,
                default: ""
            },
            imageSize: {
                type: String,
                default: "128x128"
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

        data() {
            return {
                displayType: 'string',
                hasMore: false,
                showingMore: false,
                maxArrayLength: 1,
                compactValue: null,
                fullValue: null,
                transformedValue: this.emptyString
            }
        },

        watch: {
            value: {
                handler: function (newValue) {
                    this.compactValue = null;
                    this.fullValue = null;
                    this.hasMore = false;
                    this.transformedValue = this.emptyString;
                    this.displayType = 'string';
                    this.showingMore = false;

                    this.$nextTick(() => {
                        this.transformedValue = this.transformValue();
                    });
                },
                deep: true
            }
        },

        mounted() {
            switch (this.type) {
                case 'select_multiple':
                case 'checkboxes':
                case 'tags':
                case 'relationship':
                case 'repeater':
                    this.displayType = 'tags';
                    break;
                case 'image':
                    this.displayType = 'image';
                    break;
                case 'gallery':
                    this.displayType = 'images';
                    break;
                case 'file':
                case 'files':
                    this.displayType = 'files';
                    break;
                default:
                    this.displayType = 'string';
            }


            this.transformedValue = this.transformValue();
        },

        methods: {

            toggleShowMore() {
                this.showingMore = ! this.showingMore;

                if(this.showingMore) {
                    this.transformedValue = this.fullValue;
                } else  {
                    this.transformedValue = this.compactValue;
                }
            },

            transformValue() {
                let type = upperFirst(camelCase(this.type)),
                    transformMethod = `transform${type}Value`,
                    value = this.value;

                if(typeof this[transformMethod] !== 'function') {
                    if(! value || (isArray(value) && value.length <= 0)) {
                        this.changeDisplayType('string');

                        return this.emptyString;
                    }

                    type = typeof value;

                    if(type === 'object' && isArray(value)) {
                        type = 'array';
                    }

                    type = upperFirst(camelCase(type));

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

            transformArrayValue(value, displayType = 'list', rec = false) {
                if(value.length <= 0) {
                    this.changeDisplayType('string');

                    return this.emptyString;
                }

                this.changeDisplayType(displayType);

                let newValue = [],
                    item, itemValue;

                for(item of value) {
                    if(typeof item === 'string') {
                        itemValue = item;
                    }

                    else if(item && item.label) {
                        itemValue = item.label;
                    }

                    else if(isArray(item)) {
                        itemValue = this.transformArrayValue(item, displayType, true).join(', ');
                    }

                    else {
                        itemValue = null;
                    }

                    if(typeof itemValue === 'string') {
                        newValue.push(itemValue);
                    }
                }

                newValue = compact(newValue);

                if(! rec) {
                    this.compactValue = take(newValue, this.maxArrayLength);
                    this.fullValue = newValue;
                    this.hasMore = this.compactValue.length < newValue.length;
                    newValue = this.compactValue;
                }

                return newValue;
            },

            transformDatetimeValue(value) {
                let originalValue = value;

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
                    console.log(originalValue);
                    return "INVALID DATE";
                }

                if(this.timezone === 'local') {
                    value.local();
                }
                else if(this.timezone) {
                    value.tz(this.timezone);
                }

                return flatpickr.formatDate(value.toDate(), this.dateFormat);
            },

            transformDateValue(value) {
                return this.transformDatetimeValue(value);
            },

            transformTimeValue(value) {
                return this.transformDatetimeValue(value);
            },

            transformSelectObject(value) {
                if(! isArray(value)) {
                    if(value && value.label) {
                        return [value.label];
                    }

                    this.changeDisplayType('string');

                    return this.emptyString;
                }

                return this.transformArrayValue(value, 'tags');
            },

            transformSelectMultipleValue(value) {
                return this.transformSelectObject(value);
            },

            transformCheckboxesValue(value) {
                return this.transformSelectObject(value);
            },

            transformGalleryValue(value) {
                return value;
            },

            transformImageValue(value) {
                this.changeDisplayType(this.type === 'image' ? 'image' : 'images');

                if(! value || ! value.uri) {
                    return [{
                        'alt': 'Default Image',
                        'uri': this.getDefaultImageUri(this.imageSize)
                    }];
                }

                return [value];
            },

            transformFilesValue(value) {
                if(! value || ! isArray(value) || value.length <= 0) {
                    this.changeDisplayType('string');

                    return this.emptyString;
                }

                this.compactValue = take(value, this.maxArrayLength);
                this.fullValue = value;
                this.hasMore = this.compactValue.length < value.length;

                this.changeDisplayType('files');

                return this.compactValue;
            },

            transformFileValue(value) {
                if(! value || ! value.uri) {
                    this.changeDisplayType('string');

                    return this.emptyString;
                }

                return [value];
            },

            changeDisplayType(type) {
                this.$nextTick(() => {
                    this.displayType = type;
                });
            }

        },

        computed: {

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

        },

        filters: {
            truncate(text, length, clamp) {
                text = text || '';
                clamp = clamp || '...';
                length = length || 30;

                if (text.length <= length) {
                    return text;
                }

                let tcText = text.slice(0, length - clamp.length);
                let last = tcText.length - 1;


                while (last > 0 && tcText[last] !== ' ' && tcText[last] !== clamp[0]) {
                    last -= 1;
                }

                last = last || length - clamp.length;
                tcText =  tcText.slice(0, last);

                return tcText + clamp;
            }
        }

    }

</script>