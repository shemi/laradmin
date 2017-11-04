<template>

    <div class="la-validation-set-container">
        <div class="la-validation-set">
            <b-field class="la-validation-set-item"
                     v-for="(role, index) in newValue"
                     :key="index"
            >
                <b-input placeholder="e.g. required"
                         expanded
                         v-model="newValue[index]"
                         type="text">
                </b-input>
                <p class="control">
                    <button type="button"
                            @click="deleteItem(index)"
                            class="button has-text-danger">
                        <b-icon icon="trash"></b-icon>
                    </button>
                </p>
            </b-field>

        </div>

        <div class="la-validation-set-action">
            <button type="button"
                    @click="addItem"
                    class="button is-small">
                Add Role
            </button>
        </div>
    </div>


</template>

<script>
    import {isArray, isString} from 'lodash';

    export default {

        props: {
            value: Array
        },

        data() {
            return {
                newValue: this.value
            }
        },

        watch: {
            value(newVal) {
                this.newValue = newVal
            }
        },

        created() {
            if(! isArray(this.newValue)) {
                if(isString(this.newValue)) {
                    this.newValue = [this.newValue];
                } else {
                    this.newValue = [];
                }

                this.$emit('input', this.newValue);
            }
        },

        methods: {
            addItem() {
                this.newValue.push("");

                this.$emit('input', this.newValue);
            },

            deleteItem(index) {
                this.$delete(this.newValue, index);

                this.$emit('input', this.newValue);
            }

        }

    }

</script>