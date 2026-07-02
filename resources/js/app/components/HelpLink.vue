<template>
    <Tooltip text="Help">
        <a :href target="_blank" class="flex items-center whitespace-nowrap p-2 rounded-full border border-gray-100 transition-colors hover:bg-gray-100 dark:border-gray-800 dark:hover:bg-gray-800">
            <IconSet name="help" class="size-6" />
        </a>
    </Tooltip>
</template>

<script>
import IconSet from '@/components/IconSet.vue';
import Tooltip from '@/components/Tooltip.vue';

export default {
    components: {
        IconSet,
        Tooltip
    },
    props: {
        href: {
            type: String,
            default: '/docs',
        },
    },
};
</script>
