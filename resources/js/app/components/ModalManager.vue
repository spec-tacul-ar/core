<template>
    <Component :is="component" v-bind="props" @close="close" />
</template>

<script>
import { useModalStore } from '@/stores';
import { mapState } from 'pinia';

export default {
    computed: {
        ...mapState(useModalStore, ['component', 'props']),
    },
    methods: {
        close() {
            useModalStore().close();
        },
    },
    watch: {
        '$route': {
            handler() {
                this.close();
            },
        },
    },
};
</script>
