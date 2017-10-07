<template>

    <b-field label="Simple">
        <b-select placeholder="Select a name">
            <option
                    v-for="option in options"
                    :value="option.id"
                    :key="option.id">
                {{ option.user.first_name }}
            </option>
        </b-select>
    </b-field>

</template>

<script>

    export default {



    }

</script>