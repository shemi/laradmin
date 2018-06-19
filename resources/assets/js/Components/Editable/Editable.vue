<template>
    <div class="la-editable"
         :class="{ 'is-in-edit-mode': isInEditMode, 'is-editable': ! disabled }">
        <div class="la-editable-text-label">
            <label class="label">{{ label }}:</label>
            <p  v-if="! isInEditMode"
                class="la-editable-text"
                @click.prevent="toggleEditMode">
                <la-field-renderer :form="form"
                                   :type="type"
                                   class="value"
                                   :empty-string="emptyString"
                                   :template-options="templateOptions"
                                   :browse-settings="browseSettings"
                                   :form-key="formKey">
                </la-field-renderer>
            </p>
        </div>
        <b-field v-if="isInEditMode">
            <slot></slot>
            <p class="control">
                <button type="button"
                        @click.prevent="toggleEditMode"
                        class="button is-success">
                    <b-icon icon="check"></b-icon>
                </button>
            </p>
        </b-field>
    </div>
</template>

<script>

    import MixinsLoader from '../../Helpers/MixinsLoader';
    import LaFieldRenderer from '../FieldRenderer/FieldRenderer';

    export default {

        name: 'la-editable',

        mixins: MixinsLoader.load('editable', []),

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
            label: {
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
            disabled: {
                type: Boolean,
                required: true
            }
        },

        data() {
            return {
                isInEditMode: false
            }
        },

        methods: {
            toggleEditMode() {
                if(this.disabled) {
                    this.isInEditMode = false;

                    return;
                }

                this.isInEditMode = ! this.isInEditMode;
            }
        },

        computed: {
            value() {
                let value = this.form[this.formKey];

                switch (this.type) {

                }

                return value;
            }
        },

        components: {
            LaFieldRenderer
        }

    }

</script>

<style lang="scss">
    .la-editable {
        .la-editable-text-label {
            > .label {
                display: inline-block;
            }

            .la-editable-text {
                display: inline-block;
            }

        }

        .is-in-edit-mode {
            .la-editable-text-label {
                > .label {
                    display: block;
                }
            }
        }

        &.is-editable {
            .value {
                cursor: pointer;
                text-decoration: underline;
                text-decoration-style: dashed;
                text-decoration-color: gray;
            }
        }

        + .la-editable {
            margin-top: 0.2rem;
            padding-top: 0.2rem;
            border-top: 1px solid rgb(245, 245, 245);
        }
    }

</style>