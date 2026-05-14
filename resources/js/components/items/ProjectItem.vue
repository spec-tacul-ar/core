<template>
    <div
        class="relative flex items-center flex-wrap gap-4 transition-all duration-500 p-4"
        :class="project.archived_at ? 'bg-gray-100 hover:bg-gray-200' : 'hover:bg-gray-100'">
        <div>
            <div><RouterLink :to="{name: 'projects.show', params: { project_id: project.id }}" class="text-xl font-semibold after:absolute after:inset-0">{{ project.name }}</RouterLink></div>
            <p v-if="project.archived_at" class="text-sm">Archived {{ archived_at }} ago</p>
            <p v-else class="text-sm">Updated {{ updated_at }} ago</p>
        </div>
        <div class="ml-auto">
            <div class="flex gap-1">
                <template v-if="project.requirements_count && !project.archived_at">
                    <div v-if="project.tasks_count > 0" class="inline-flex gap-2 leading-none p-1 pr-2 text-sm rounded-full items-center bg-green-100 text-green-500">
                        <IconSet name="success" /> {{ Math.round((project.requirements_all_tasks_complete_count / project.requirements_with_tasks_count) * 100) }}%
                    </div>

                    <div v-if="project.unknowns_count > 0" class="inline-flex gap-2 leading-none p-1 pr-2 text-sm rounded-full items-center bg-yellow-100 text-yellow-600">
                        <IconSet name="question" /> {{ project.unknowns_count }}
                    </div>

                    <div v-if="project.blocked_requirements_count > 0" class="inline-flex gap-2 leading-none p-1 pr-2 text-sm rounded-full items-center bg-red-100 text-red-500">
                        <IconSet name="warning" /> {{ project.blocked_requirements_count }}
                    </div>
                </template>

                <SpinnerButton
                    v-if="project.archived_at"
                    type="button"
                    class="relative z-10 btn btn-primary-outline"
                    :loading="is_unarchiving"
                    icon="archive"
                    @click.stop="unarchive">

                    Unarchive
                </SpinnerButton>
            </div>
        </div>
    </div>
</template>

<script>
import IconSet from '@/components/IconSet.vue';
import Project from '@/stores/models/Project';
import SpinnerButton from '@/components/SpinnerButton.vue';
import { formatDistance } from 'date-fns';
import { useAlertsStore } from '@/stores';

export default {
    components: {
        IconSet,
        SpinnerButton,
    },
    data() {
        return {
            is_unarchiving: false,
        };
    },
    computed: {
        archived_at() {
            return formatDistance(this.project.archived_at, new Date());
        },
        updated_at() {
            return formatDistance(this.project.updated_at, new Date());
        },
    },
    inject: ['api'],
    methods: {
        unarchive() {
            this.is_unarchiving = true;

            this.api.post('projects/' + this.project.id + '/unarchive')
                .then((result) => {
                    Project.repository().save(result.data);

                    this.$router.push({ name: 'projects.show', params: {project_id: this.project.id}});

                    useAlertsStore().push('Project unarchived.');
                })
                .finally(() => this.is_unarchiving = false);
        },
    },
    props: [
        'project'
    ],
};
</script>
