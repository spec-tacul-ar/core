<template>
    <div
        ref="trigger"
        @mouseenter="show"
        @mouseleave="hide">

        <slot />

        <Transition
            v-if="text"
            enter-from-class="opacity-0"
            enter-active-class="transition-opacity delay-500"
            leave-active-class="transition-opacity"
            leave-to-class="opacity-0">

            <Teleport to="body">
                <div
                    ref="tooltip"
                    v-if="is_visible"
                    v-text="text"
                    class="fixed -translate-x-1/2 mt-2 p-1 px-2 bg-black/75 text-white text-sm rounded whitespace-nowrap z-30"
                    :style="{ left: x + 'px', top: y + 'px' }">

                </div>
            </Teleport>
        </Transition>
    </div>
</template>

<script>
export default {
    data() {
        return {
            is_visible: false,
            x: 0,
            y: 0
        };
    },
    methods: {
        show() {
            if (!this.enabled) {
                return;
            }

            this.is_visible = true;
            this.move();
        },
        hide() {
            this.is_visible = false;
        },
        move() {
            const trigger = this.$refs.trigger;

            // The trigger may have been removed from the DOM.
            if (trigger) {
                const bounds = trigger.getBoundingClientRect();

                this.x = bounds.left + (bounds.width / 2);
                this.y = bounds.bottom;
            }
        }
    },
    mounted() {
        window.addEventListener('resize', this.move);
    },
    props: {
        enabled: {
            type: Boolean,
            default: true,
        },
        text: String,
    },
    unmounted() {
        window.removeEventListener('resize', this.move);
    },
};
</script>
