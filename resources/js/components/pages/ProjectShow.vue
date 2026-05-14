<template>
    <DefaultLayout expanded>
        <template #toggles="{ openSidebar, closeSidebar, show_sidebar }">
            <SidebarSwitches :project :sidebar :open="show_sidebar" @change="changeSidebar($event, show_sidebar, openSidebar, closeSidebar)" />
        </template>

        <template #menu>
            <ProjectShowMenu :project />
        </template>

        <template #sidebar="{ navigate }">
            <ProjectOutline v-if="sidebar === 'outline'" :project="project" class="project-outline" @navigation="navigate" />
            <ProjectFilters v-if="sidebar === 'filters'" :project />
        </template>

        <div v-if="project.archived_at" class="flex items-center gap-3 border border-gray-300 bg-gray-100 text-gray-700 rounded-lg p-4 mb-8 print:hidden">
            <IconSet name="warning" class="size-5 shrink-0" />
            <p class="font-semibold mr-auto">This project is archived and cannot be edited.</p>

            <SpinnerButton
                v-if="project.can_restore"
                type="button"
                class="btn btn-primary-outline"
                :loading="is_restoring"
                icon="archive"
                @click="restore">
                
                Restore
            </SpinnerButton>

            <button v-if="project.my_role === 'owner'" type="button" class="btn btn-danger-outline" @click="openProjectDeleteModal">
                <IconSet name="trash" />
            </button>
        </div>
        
        <section id="introduction" class="mb-8">
            <div class="flex flex-wrap md:flex-nowrap items-center mb-8 sm:pl-8 print:pl-0">
                <h1 class="font-display font-semibold text-4xl mr-auto">{{ project.name }}</h1>

                <RouterLink v-if="project.can_write" :to="{ name: 'projects.edit' }" class="btn btn-primary"><IconSet name="edit" /> Edit project</RouterLink>
            </div>

            <div v-if="project.description" class="bg-white p-8 shadow rounded-3xl print:p-0 print:shadow-none">
                <RichText v-if="project.description" :markup="project.description" />
            </div>
        </section>

        <section id="users" class="mb-8">
            <div class="flex justify-between flex-wrap md:flex-nowrap items-center mb-4 ml-8 print:mx-0">
                <h2 class="text-3xl"><a href="#users">Users</a></h2>

                <RouterLink v-if="project.can_write" :to="{ name: 'projects.actors.create' }" class="btn btn-primary print:hidden" replace><IconSet name="plus-lg" /> Add user</RouterLink>
            </div>

            <div class="bg-white p-8 shadow rounded-3xl print:p-0 print:shadow-none space-y-4">
                <ActorItem :actor="actor" v-for="actor in actors" :key="actor.id" />
            </div>
        </section>

        <section id="features" class="mb-4">
            <div class="flex justify-between flex-wrap md:flex-nowrap items-center mb-4 ml-8 print:mx-0">
                <h2 class="text-3xl"><a href="#features">Features</a></h2>

                <RouterLink v-if="project.can_write" :to="{ name: 'projects.features.create' }" class="btn btn-primary"><IconSet name="plus-lg" /> Add feature</RouterLink>
            </div>

            <FeatureItem :feature="feature" v-for="feature in features" :key="feature.id" />
        </section>

        <div v-if="project.total_estimate > 0" class="bg-white p-8 shadow rounded-3xl print:p-0 print:shadow-none">
            <section>
                <h2 class="text-3xl mb-4"><a href="#features">Summary</a></h2>

                <table class="w-full">
                    <tbody>
                        <tr v-for="feature in features" :key="feature.id">
                            <td colspan="2" class="py-2">{{ feature.name }}</td>
                            <td class="text-right py-2">{{ feature.has_tasks ? feature.requirements_estimate + ' h' : '-' }}</td>
                        </tr>
                    </tbody>
                    <tfoot class="border-y border-gray-200">
                        <tr class="text-right">
                            <th colspan="2" class="text-right py-2">Total</th>
                            <td class="py-2">{{ project.total_estimate }} hours</td>
                        </tr>
                    </tfoot>
                </table>
            </section>
        </div>
    </DefaultLayout>
</template>

<script>
import DefaultLayout from '@/components/layouts/DefaultLayout.vue';
import FeatureItem from '@/components/items/FeatureItem.vue';
import IconSet from '@/components/IconSet.vue';
import RichText from '@/components/RichText.vue';
import ProjectFilters from '@/components/sidebars/ProjectFilters.vue';
import ProjectDelete from '@/components/modals/ProjectDelete.vue';
import ProjectOutline from '@/components/sidebars/ProjectOutline.vue';
import ProjectShowMenu from "@/components/navigation/ProjectShowMenu.vue";
import Project from '@/stores/models/Project';
import SidebarSwitches from "@/components/navigation/SidebarSwitches.vue";
import SpinnerButton from '@/components/SpinnerButton.vue';
import ActorItem from '@/components/items/ActorItem.vue';
import { useAlertsStore, useModalStore } from '@/stores';
import { inject } from 'vue';

export default {
    components: {
        DefaultLayout,
        FeatureItem,
        IconSet,
        ProjectFilters,
        ProjectOutline,
        ProjectShowMenu,
        RichText,
        SidebarSwitches,
        SpinnerButton,
        ActorItem,
    },
    inject: ['api'],
    computed: {
        features() {
            return this.project.features.sortBy('id').sortBy('weight');
        },
        project() {
            return Project.repository().find(this.project_id);
        },
        actors() {
            return this.project.actors.sortBy('id').sortBy('weight');
        },
    },
    data() {
        return {
            is_restoring: false,
            sidebar: 'outline',
        };
    },
    methods: {
        changeSidebar(next, is_open, openSidebar, closeSidebar) {
            if (next === this.sidebar && is_open) {
                closeSidebar();
                return;
            }

            if (!is_open) {
                openSidebar();
            }

            this.sidebar = next;
        },
        openProjectDeleteModal() {
            useModalStore().open(ProjectDelete, {project: this.project});
        },
        restore() {
            this.is_restoring = true;

            this.api.post('projects/' + this.project.id + '/unarchive')
                .then((result) => {
                    Project.repository().save(result.data);

                    useAlertsStore().push('Project restored.');
                })
                .finally(() => this.is_restoring = false);
        },
        checkScrollPosition() {
            this.is_scrolled_to_top = window.scrollY === 0;
        },
        scrollToBottom() {
            window.scrollTo({ top: document.body.scrollHeight });
        },
        scrollToTop() {
            window.scrollTo({ top: 0 });
        },
    },
    mounted() {
        this.checkScrollPosition();
        
        window.addEventListener('scroll', this.checkScrollPosition);
    },
    props: {
        'project_id': {
            'type': String,
            'required': true
        }
    },
    provide() {
        return {
            project: this.project,
        };
    },
    async setup(props) {
        const api = inject('api');
        const store = Project.repository();
        const project = store.find(props.project_id);

        if (!project || !project.is_hydrated) {
            await api.get('projects/' + props.project_id + '/read', {query: {'hydrated': true}})
                .then((result) => {
                    if (project) {
                        Project.repository().save({...result.data, is_hydrated: true});
                        return;
                    }

                    Project.repository().save({...result.data, is_hydrated: true});
                });
        }
    },
    unmounted() {
        window.removeEventListener('scroll', this.checkScrollPosition);
    },
    watch: {
        '$route': {
            immediate: true,
            handler(route) {
                if (!route.meta.title) {
                    document.title = this.project.name;
                }
            },
        },
        is_loading(new_value) {
            if (!new_value) {
                const hash = this.$route.hash.substring(1);
                
                this.$nextTick(() => {
                    const element = document.getElementById(hash);

                    if (element) {
                        element.scrollIntoView();
                    }
                });
            }
        },
    },
};
</script>
