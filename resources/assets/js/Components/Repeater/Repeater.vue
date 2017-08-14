<template>

    <div class="fa-repeater">

        <div class="table-wrapper">
            <table class="table is-bordered is-striped has-mobile-cards">

                <thead>
                <tr>
                    <th class="checkbox-cell">

                    </th>
                    <th v-for="column in columns" :style="{ width: column.width + 'px' }">
                        <div class="th-wrap">
                            {{ column.label }}
                        </div>
                    </th>
                </tr>
                </thead>

                <tbody v-if="rows.length">
                <tr v-for="(row, index) in rows">

                    <td class="checkbox-cell" >
                        {{ index + 1 }}
                    </td>

                    <slot :row="row" :index="index"></slot>
                </tr>
                </tbody>
                <tbody v-else>
                <tr>
                    <td>NN</td>
                </tr>
                </tbody>

            </table>
        </div>

        <div class="level">
            <div class="level-left"></div>
            <div class="level-right">
                <button type="button" class="button" @click.prevent="addRow">
                    <span>Add Row</span>
                </button>
            </div>
        </div>

    </div>

</template>

<script>
    export default {

        props: {
            value: {
                type: Array,
                required: true
            },
            form: {
                type: Object,
                required: true
            }
        },

        data() {
            return {
                _isRepeater: true,
                columns: []
            }
        },

        methods: {
            addRow() {
                this.value.push({});
            }
        },

        computed: {
            rows() {
                return this.value;
            }
        }

    }
</script>