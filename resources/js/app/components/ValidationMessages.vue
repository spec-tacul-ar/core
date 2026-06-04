<template>
    <div v-if="messages.length" class="bg-red-400 text-white p-2 mb-4">
        <ul>
            <li v-for="message in messages" :key="message">{{ message }}</li>
        </ul>
    </div>
</template>

<script>
export default {
    computed: {
        messages() {
            const exclude = this.exclude instanceof Array ? this.exclude : Object.keys(this.exclude);

            return Object.keys(this.errors)
                .filter(key => !exclude.includes(key))
                .map(key => this.errors[key][0]);
        },
    },
    props: {
        errors: Object,
        exclude: {
            type: [Array, Object],
            default: () => [],
        },
    },
};
</script>
