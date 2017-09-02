<template>

    <div class="media" :class="{'is-selected': isSelected}">
        <div class="media-left" v-if="hasImage">
            <p class="image is-64x64">
                <img :src="image">
            </p>
        </div>

        <div class="media-content">
            <div class="content">
                <p>
                    <strong>{{ label }}</strong>
                    <span v-if="hasSubLabels" v-html="subLabels"></span>
                </p>
            </div>
        </div>

        <div class="media-right" v-if="! isSelected">
            <slot></slot>
        </div>

    </div>

</template>

<script>
    export default {

        props: {
            label: {
                type: String,
                required: true
            },
            isSelected: {
                type: Boolean,
                required: false,
                default: false
            },
            extraLabels: {
                type: [Object],
                required: false
            },
            image: {
                type: String,
                default: "https://placeholdit.co//i/64x64?bg=efefef&fc=000"
            },
        },

        data() {
            return {

            }
        },

        created() {
            if (! this.$parent.$data._isRelationship) {
                this.$destroy();
                throw new Error('You should wrap laRelationshipRow on a laRelationship');
            }
        },

        computed: {
            hasSubLabels() {
                return this.extraLabels && Object.values(this.extraLabels).length > 0;
            },

            subLabels() {
                return '<br>'+Object.values(this.extraLabels).join('<br>');
            },

            hasImage() {
                return this.$parent.$data.hasImage;
            }

        }

    }
</script>