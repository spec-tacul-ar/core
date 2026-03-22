<template>
    <div class="flex h-full flex-col relative">
        <div class="fixed top-0 bg-white shadow-lg shadow-white w-full max-w-(--sidepanel-width) h-16 flex items-center px-4 sm:px-8">
            <div class="flex items-center w-full -ml-2">
                <slot name="toolbar"></slot>
            </div>

            <button type="button" class="block p-2 -mr-2" @click="$emit('close')">
                <IconSet name="x-lg" class="size-6" />
            </button>
        </div>

        <div class="h-full p-4 sm:p-8 sm:pt-4 mt-16 overflow-auto">
            <div :class="{ 'pb-16': has_buttons }">
                <slot />
            </div>

            <div v-if="has_buttons" class="fixed bottom-0 w-full -mx-8">
                <div class="h-4 bg-linear-to-b from-white/0 to-white/100"></div>
                <div class="bg-white border-t border-gray-200 py-4 px-8">
                    <slot name="buttons"></slot>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import IconSet from '@/components/IconSet.vue';

export default {
    components: {
        IconSet,
    },
    computed: {
        has_buttons() {
            return !!this.$slots.buttons;
        },
    },
    mounted() {
        const element = this.$el.querySelector('input');

        if (element) {
            element.focus();
        }
    }
};

</script>
