<template>
    <DefaultLayout>
        <Announcements />

        <section>
            <div class="flex justify-between flex-wrap items-end gap-4 mb-4 sm:pl-4">
                <h1 class="font-display font-semibold text-4xl">Projects</h1>
                
                <RouterLink v-if="!is_loading_projects && projects.isNotEmpty()" :to="{ name: 'projects.create' }" class="btn btn-primary">Create project</RouterLink>
            </div>

            <LoadingSpinner label="Loading projects" v-if="is_loading_projects" />

            <div
                v-if="!is_loading_projects && projects.isEmpty()"
                class="flex flex-col items-center gap-2 bg-white/25 border-2 border-gray-200 border-dashed rounded-2xl p-4 mb-4">

                <IconSet name="create-project" class="size-16 text-gray-600" />

                <h2 class="font-semibold">Welcome to Spectacular!</h2>

                <p>Get started by creating your first project.</p>

                <div class="flex flex-wrap items-center justify-center gap-4">
                    <RouterLink :to="{ name: 'projects.create' }" class="btn btn-primary">Create project</RouterLink>

                    <SpinnerButton
                        type="button"
                        class="btn btn-primary-outline"
                        :loading="is_creating_demo_project"
                        @click="createDemoProject">
                        Create demo project
                    </SpinnerButton>
                </div>
            </div>

            <Card v-if="projects.isNotEmpty() || invitations.isNotEmpty()" class="divide-y divide-gray-300">
                <InvitationItem v-for="invitation in invitations" :key="invitation.id" :invitation="invitation" />
                <ProjectItem v-for="project in projects" :key="project.id" :project="project" />
            </Card>
        </section>
    </DefaultLayout>
</template>

<script>
import Announcements from '@/components/Announcements.vue';
import Card from '@/components/Card.vue';
import DefaultLayout from '@/components/layouts/DefaultLayout.vue';
import IconSet from '@/components/IconSet.vue';
import Invitation from '@/stores/models/Invitation';
import InvitationItem from '@/components/items/InvitationItem.vue';
import LoadingSpinner from '@/components/LoadingSpinner.vue';
import ProjectItem from '@/components/items/ProjectItem.vue';
import SpinnerButton from '@/components/SpinnerButton.vue';
import Project from '@/stores/models/Project';
import { useAuthStore } from '@/stores';

export default {
    inject: ['api'],
    components: {
        Announcements,
        Card,
        DefaultLayout,
        IconSet,
        InvitationItem,
        LoadingSpinner,
        ProjectItem,
        SpinnerButton,
    },
    data() {
        return {
            'is_creating_demo_project': false,
            'is_loading_projects': false,
        };
    },
    computed: {
        invitations() {
            return Invitation.repository().collection.where('email', useAuthStore().account.email).sortBy('name');
        },
        is_solo() {
            return !!useAuthStore().account.is_solo;
        },
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

        if (!this.is_solo) {
            // Invitations
            this.api.get('invitations/browse', { query: { account_id: useAuthStore().account.id }})
                .then((result) => {
                    Invitation.repository().saveMany(result.data);
                });
        }
    },
    methods: {
        createDemoProject() {
            this.is_creating_demo_project = true;

            this.api.post('projects/demo')
                .then((result) => {
                    const project = result.data;

                    Project.repository().save(project);

                    this.$router.push({ name: 'projects.show', params: { project_id: project.id } });
                })
                .finally(() => this.is_creating_demo_project = false);
        },
    },
};
</script>
