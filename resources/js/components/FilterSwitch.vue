<template>
    <button
        type="button"
        class="w-full flex items-center gap-2 text-left hover:bg-gray-100 px-4 py-2"
        :class="{
            'pl-8': value == null,
            'bg-gray-50': value != null
        }"
        @click="toggleFilter()">
        
        <div class="">
            <IconSet v-if="value === true" name="plus-lg" class="size-4" />
            <IconSet v-if="value === false" name="minus-lg" class="size-4" />
        </div>

        <slot />
    </button>
</template>

<script>
import IconSet from '@/components/IconSet.vue';

export default {
    components: {
        IconSet,
    },
    methods: {
        toggleFilter() {
            if (this.value === null || this.value === undefined) {
                this.$emit('change', true);
                return;
            }

            if (this.value === true) {
                this.$emit('change', false);
                return;
            }

            this.$emit('change', null);
        },
    },
    props: ['value']
};
</script>
