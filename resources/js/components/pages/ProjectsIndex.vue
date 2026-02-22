<template>
    <DefaultLayout>
        <section>
            <div class="flex justify-between flex-wrap items-end gap-4 mb-4 sm:pl-4">
                <h1 class="font-display font-semibold text-4xl">Projects</h1>
                
                <RouterLink :to="{ name: 'projects.create' }" class="btn btn-primary">Create project</RouterLink>
            </div>

            <LoadingSpinner label="Loading projects" v-if="is_loading_projects" />

            <!-- TODO: CTA for creating a new project if none exist -->

            <Card class="divide-y divide-gray-300">
                <ProjectItem v-for="project in projects" :key="project.id" :project="project" />
            </Card>
        </section>
    </DefaultLayout>
</template>

<script>
import Card from '@core/components/Card.vue';
import DefaultLayout from '@core/components/layouts/DefaultLayout.vue';
import IconSet from '@core/components/IconSet.vue';
import LoadingSpinner from '@core/components/LoadingSpinner.vue';
import ProjectItem from '@core/components/items/ProjectItem.vue';
import { formatDistance } from 'date-fns';
import { useProjectsStore } from '@core/stores';

export default {
    components: {
        Card,
        DefaultLayout,
        IconSet,
        LoadingSpinner,
        ProjectItem,
    },
    data() {
        return {
            'is_loading_projects': false,
        };
    },
    computed: {
        projects: () => useProjectsStore().collection.sortBy('name'),
    },
    mounted() {
        // Projects
        if (this.projects.isEmpty()) {
            this.is_loading_projects = true;
        }

        this.$api.get('projects/browse')
            .then((result) => {
                useProjectsStore().saveMany(result.data);
            })
            .finally(() => this.is_loading_projects = false);
    },
};
</script>
