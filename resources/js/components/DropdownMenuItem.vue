<template>
    <div class="relative flex gap-3 items-center px-4" :class="colour_classes">
        <IconSet v-if="icon && !loading" :name="icon" />

        <svg v-if="icon && loading" class="animate-spin size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>

        <a v-if="href && !to" :href="href" v-bind="$attrs" class="block py-1 after:absolute after:inset-0"><slot /></a>

        <RouterLink v-if="!href && to" :to="to" v-bind="$attrs" class="block py-1 after:absolute after:inset-0"><slot /></RouterLink>

        <button v-if="!href && !to" v-bind="$attrs" :disabled="disabled" type="button" class="block w-full text-left py-1">
            <slot />
        </button>
    </div>
</template>

<script>
import IconSet from '@/components/IconSet.vue';

export default {
    components: {
        IconSet,
    },
    computed: {
        colour_classes() {
            if (this.disabled && this.danger) {
                return 'text-red-200';
            }

            if (this.disabled) {
                return 'text-gray-400';
            }

            if (this.danger) {
                return 'text-red-400 hover:bg-red-400 hover:text-white';
            }

            return 'hover:bg-gray-100';
        }
    },
    inheritAttrs: false,
    props: {
        danger: Boolean,
        disabled: Boolean,
        href: String,
        icon: String,
        loading: Boolean,
        to: Object,
    },
};
</script>

