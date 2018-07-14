<template>

    <div class="card">
        <header class="card-header" @click="toggleRow">
            <div class="card-header-number">
                <span class="number la-drag-handle">{{ index + 1 }}</span>
            </div>

            <div class="card-header-title">
                <div class="label-action">
                    <span class="label">{{ row[collapseFieldKey] }}</span>
                    <div class="actions">
                        <a class="action has-text-primary">{{ isActive ? 'Close' : 'Open' }}</a>
                        <a class="action has-text-danger" @click.prevent.stop="$emit('delete-row', index)">Delete</a>
                    </div>
                </div>
            </div>
            <a class="card-header-icon">
                <b-icon :icon="'angle-' + (isActive ? 'up' : 'down')"></b-icon>
            </a>
        </header>

        <div class="card-content" v-show="isActive">
            <slot :row="row" :index="index"></slot>
        </div>

    </div>

</template>

<script>
    export default {

        name: 'la-repeater-row',

        props: {
            collapseFieldKey: {
                type: String
            },
            row: {
                type: Object
            },
            index: {
                type: Number
            }
        },

        data() {
            return {
                isActive: false
            }
        },

        methods: {
            toggleRow() {
                this.isActive = ! this.isActive;
            }
        },

        created() {
            if(this.row.laResentCreated) {
                this.row.laResentCreated = false;
                this.isActive = true;
            }

        }

    }
</script>