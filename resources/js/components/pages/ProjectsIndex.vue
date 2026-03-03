<template>
    <DefaultLayout>
        <section>
            <div class="flex justify-between flex-wrap items-end gap-4 mb-4 sm:pl-4">
                <h1 class="font-display font-semibold text-4xl">Projects</h1>
                
                <RouterLink v-if="!is_loading_projects && projects.isNotEmpty()" :to="{ name: 'projects.create' }" class="btn btn-primary">Create project</RouterLink>
            </div>

            <LoadingSpinner label="Loading projects" v-if="is_loading_projects" />

            <div
                v-if="!is_loading_projects && projects.isEmpty()"
                class="flex flex-col items-center gap-2 bg-white/25 border-2 border-gray-200 border-dashed rounded-2xl p-4">

                <IconSet name="create-project" class="size-16 text-gray-600" />

                <h2 class="font-semibold">Welcome to Spectacular!</h2>

                <p>Get started by creating your first project</p>

                <RouterLink :to="{ name: 'projects.create' }" class="btn btn-primary">Create project</RouterLink>
            </div>

            <Card v-if="projects.isNotEmpty()" class="divide-y divide-gray-300">
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
import Project from '@core/stores/models/Project';

export default {
    inject: ['api'],
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
        projects() {
            return Project.repository().collection.sortBy('name');
        },
    },
    mounted() {
        // Projects
        if (this.projects.isEmpty()) {
            this.is_loading_projects = true;
        }

        this.api.get('projects/browse')
            .then((result) => {
                Project.repository().saveMany(result.data);
            })
            .finally(() => this.is_loading_projects = false);
    },
};
</script>
