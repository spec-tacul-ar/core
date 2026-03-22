<template>
    <div
        @wheel.prevent
        @touchmove.prevent
        @scroll.prevent
        class="fixed inset-0 flex justify-center items-center bg-black/50 backdrop-blur z-30 print:hidden">

        <div ref="modal" class="bg-white p-8 max-w-3xl shadow-lg">
            <button v-if="!locked" type="button" class="block p-2 float-right -m-2" @click="trap.deactivate()">
                <IconSet name="x-lg" class="size-6" />
            </button>

            <slot />
        </div>
    </div>
</template>

<script>
import IconSet from '@/components/IconSet.vue';
import { createFocusTrap } from 'focus-trap';

export default {
    components: {
        IconSet,
    },
    data() {
        return {
            trap: null,
        };
    },
    methods: {
        close() {
            if (!this.locked) {
                this.$emit('close');
            }
        },
    },
    mounted() {
        this.$nextTick(() => {
            this.trap = createFocusTrap(this.$refs.modal, {
                clickOutsideDeactivates: true,
                onPostDeactivate: () => this.close(),
            }).activate();
        });
    },
    beforeUnmount() {
        this.trap.deactivate({ onPostDeactivate: null });
    },
    props: {
        locked: Boolean
    },
};
</script>
