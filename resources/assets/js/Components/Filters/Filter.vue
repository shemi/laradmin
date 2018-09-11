<template>

    <b-field :label="label">
        <b-select placeholder="---"
                  v-model="newValue"
                  @input="onChange"
                  expanded
        >

            <option :value="null">
                ---
            </option>

            <option v-for="option in options" :value="option.key">
                {{ option.label }}
            </option>

        </b-select>
    </b-field>

</template>

<script>
    import FilterMixin from "./FilterMixin";

    export default {

        name: 'la-filter',

        mixins: [FilterMixin]

    }

</script>