<template>
    <div class="flex justify-end gap-1 mb-3 -mx-4">
        <RouterLink v-if="feature.project.can_write" :to="{ name: 'projects.features.requirements.create', params: { feature_id: feature.id }}" class="btn btn-primary"><IconSet name="plus-lg" /> Add requirement</RouterLink>
        
        <DropdownMenu>
            <DropdownMenuItem v-if="feature.project.can_write" :to="{ name: 'projects.features.edit', params: { feature_id: feature.id }}" icon="edit">Edit</DropdownMenuItem>
            <DropdownMenuItem :to="{ name: 'projects.features.feedback', params: { feature_id: feature.id }}" icon="feedback">Feedback</DropdownMenuItem>

            <slot name="menu" />

            <DropdownMenuItem v-if="project.can_write" @click="openFeatureDeleteModal" icon="trash" danger>Delete</DropdownMenuItem>
        </DropdownMenu>
    </div>
</template>

<script>
import DropdownMenu from '@/components/DropdownMenu.vue';
import DropdownMenuItem from '@/components/DropdownMenuItem.vue';
import FeatureDelete from '@/components/modals/FeatureDelete.vue';
import IconSet from '@/components/IconSet.vue';
import { useModalStore } from '@/stores';

export default {
    components: {
        DropdownMenu,
        DropdownMenuItem,
        IconSet,
    },
    computed: {
        has_filters() {
            return this.project.filters.has_filters;
        },
    },
    inject: ['project'],
    methods: {
        openFeatureDeleteModal() {
            useModalStore().open(FeatureDelete, {feature: this.feature});
        },
    },
    props: ['feature'],
};
</script>
