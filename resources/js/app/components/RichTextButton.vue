<template>
    <button
        type="button"
        :title="title"
        class="p-2 rounded disabled:opacity-50"
        :class="editor && (editor.isActive(this.name) || active) ? 'bg-gray-200' : 'bg-gray-50'">

        <IconSet :name="icon" />
    </button>
</template>

<script>
import IconSet from '@/components/IconSet.vue';

export default {
    components: {
        IconSet,
    },
    computed: {
        title() {
            if (this.label) {
                return this.label;
            }

            return this.name.charAt(0).toUpperCase() + this.name.slice(1);
        },
    },
    props: {
        active: Boolean,
        editor: Object,
        icon: String,
        label: String,
        name: String,
    },
};
</script>
