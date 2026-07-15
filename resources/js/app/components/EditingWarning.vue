<template>
    <button v-if="editors.length" type="button" class="relative p-2 text-orange-600" @click="toggle">
        <IconSet name="warning" class="size-6" />

        <Transition
            enter-from-class="!opacity-0"
            enter-active-class="transition-opacity"
            leave-active-class="transition-opacity"
            leave-to-class="!opacity-0">

            <div
                v-if="message && !hide_message"
                class="absolute right-0 bg-orange-600 rounded-md shadow-md text-white text-sm mt-1 mr-2 w-max max-w-48 p-2 opacity-75">

                {{ message }}
            </div>
        </Transition>
    </button>
</template>

<script>
import IconSet from '@/components/IconSet.vue';

export default {
    components: {
        IconSet,
    },
    computed: {
        message() {
            const names = this.editors.map(editor => editor.name);

            if (names.length === 0) {
                return null;
            }

            if (names.length === 1) {
                return names[0] + ' is also editing this resource.';
            }

            if (names.length === 2) {
                return names[0] + ' and ' + names[1] + ' are editing this resource.';
            }

            const others = names.length - 2;

            return names[0] + ', ' + names[1] + ' and ' + others + ' ' + (others === 1 ? 'other' : 'others') + ' are editing this resource.';
        },
    },
    data() {
        return {
            hide_message: false,
        };
    },
    methods: {
        toggle() {
            this.hide_message = !this.hide_message;
        },
    },
    props: {
        editors: {
            type: Array,
            required: true,
        },
    },
};
</script>
