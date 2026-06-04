<template>
    <div v-if="this.alert.type === 'danger'" class="flex items-center gap-4 text-white bg-red-600 rounded shadow p-2 px-4 mt-2">
        <IconSet name="danger" class="size-4 shrink-0" /> {{ alert.text }}
    </div>
    <div v-else-if="this.alert.type === 'warning'" class="flex items-center gap-4 text-white bg-orange-600 rounded shadow p-2 px-4 mt-2">
        <IconSet name="warning" class="size-4 shrink-0" /> {{ alert.text }}
    </div>
    <div v-else class="flex items-center gap-4 text-white bg-green-600 rounded shadow p-2 px-4 mt-2">
        <IconSet name="success" class="size-4 shrink-0" /> {{ alert.text }}
    </div>
</template>

<script>
import { useAlertsStore } from '@/stores';
import IconSet from '@/components/IconSet.vue';

export default {
    components: {
        IconSet,
    },
    mounted() {
        setTimeout(() => useAlertsStore().shift(), 5 * 1000);
    },
    props: {
        'alert': Object,
    }
};
</script>
