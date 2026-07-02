<template>    
    <div class="flex items-center gap-2 text-sm">
        <slot name="after" />

        <Tooltip text="Outline">
            <button
                type="button"
                class="flex items-center gap-2 p-2 rounded-full border border-gray-100 transition-colors hover:bg-gray-100 dark:border-gray-800 dark:hover:bg-gray-800"
                :class="[ open && sidebar === 'outline' ? 'bg-gray-200 dark:bg-gray-800' : '' ]"
                @click="$emit('change', 'outline')">

                <IconSet name="outline" class="size-6 shrink-0" />
            </button>
        </Tooltip>
        
        <Tooltip text="Filters">
            <button
                type="button"
                class="relative flex items-center gap-2 p-2 rounded-full border border-gray-100 transition-colors hover:bg-gray-100 dark:border-gray-800 dark:hover:bg-gray-800"
                :class="[ open && sidebar === 'filters' ? 'bg-gray-200 dark:bg-gray-800' : '' ]"
                @click="$emit('change', 'filters')">

                <IconSet name="filter" class="size-6 shrink-0" />

                <div v-if="has_filters" class="absolute top-0 right-0 size-3 bg-indigo-600 ring-2 ring-white rounded-full dark:ring-gray-950"></div>
            </button>
        </Tooltip>

        <slot name="after" />
    </div>
</template>

<script>
import IconSet from '@/components/IconSet.vue';
import Tooltip from '@/components/Tooltip.vue';

export default {
    components: {
        IconSet,
        Tooltip
    },
    computed: {
        has_filters() {
            return this.project.filters.has_filters;
        },
    },
    props: ['open', 'project', 'sidebar'],
};
</script>
