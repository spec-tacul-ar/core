<template>
    <Tooltip :enabled="!has_slot" :text="tooltip">
        <RouterLink
            :to="destination"
            class="flex items-center whitespace-nowrap p-2 rounded-full border border-gray-100 transition-colors hover:bg-gray-100 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-gray-800"
            :class="{ 'bg-gray-200 dark:bg-gray-800': is_active }">

            <IconSet :name="icon" class="size-6" />

            <span v-if="has_slot" class="max-lg:hidden mx-2">     
                <slot />
            </span>
        </RouterLink>
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
    computed: {
        destination() {
            if (this.is_active) {
                const matched = this.$route.matched;
                
                if (matched.length > 1) {
                    return matched[matched.length - 2]; // Finds the parent
                }
            }

            return this.to;
        },
        has_slot() {
            return this.$slots.default;
        },
        is_active() {
            return this.$route.name === this.to.name;
        },
    },
    props: ['icon', 'to', 'tooltip'],
};
</script>
