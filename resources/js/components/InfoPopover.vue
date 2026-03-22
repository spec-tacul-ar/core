<template>
    <div class="relative">
        <button
            type="button"
            class="relative"
            ref="button"
            tabindex="2"
            @blur="is_open = false"
            @click="is_open = !is_open">

            <IconSet name="info" />
        </button>

        <Transition
            enter-from-class="opacity-0"
            enter-active-class="transition-opacity"
            leave-active-class="transition-opacity"
            leave-to-class="opacity-0">

            <div v-if="is_open" class="absolute right-0 top-0 w-72 z-10 bg-gray-50 border border-gray-200 rounded shadow-md mr-6 p-4 z-30" ref="content">
                <slot></slot>
            </div>
        </Transition>
    </div>
</template>

<script>
import IconSet from '@/components/IconSet.vue';

export default {
    components: {
        IconSet,
    },
    data() {
        return {
            'is_open': false,
        };
    },
    watch: {
        is_open(is_open) {
            if (is_open) {
                // Fix for Safari.
                // https://stackoverflow.com/a/61246636
                this.$refs.button.focus();
            }
        }
    }
};
</script>
