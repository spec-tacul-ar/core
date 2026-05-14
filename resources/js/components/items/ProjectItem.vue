<template>
    <div
        class="relative flex flex-wrap gap-4 transition-all duration-500"
        :class="project.archived_at ? 'items-center px-4 py-3' : 'items-start p-4'">

        <div>
            <RouterLink
                :to="{name: 'projects.show', params: { project_id: project.id }}"
                class="font-semibold hover:underline after:absolute after:inset-0"
                :class="project.archived_at ? 'text-base' : 'text-2xl'">

                {{ project.name }}
            </RouterLink>
            
            <p v-if="project.archived_at" class="text-sm text-gray-500">Archived {{ archived_at }} ago</p>
        </div>

        <div class="relative z-10 ml-auto text-right">
            <div class="flex justify-end gap-1">
                <div v-if="!solo_mode && !project.archived_at && project.my_role_name" class="inline-flex gap-2 leading-none px-2 h-6 text-sm rounded-full items-center bg-gray-200 text-gray-500 cursor-default">
                    {{ project.my_role_name }}
                </div>

                <Tooltip v-if="!solo_mode && project.contributors_count > 0 && !project.archived_at" text="Contributors">
                    <div class="inline-flex gap-2 leading-none px-1 pr-2 h-6 text-sm rounded-full items-center bg-gray-200 text-gray-500 cursor-default">
                        <IconSet name="person-circle" /> {{ project.contributors_count }}
                    </div>
                </Tooltip>

                <template v-if="project.requirements_count && !project.archived_at">
                    <Tooltip v-if="project.tasks_count > 0" text="Completed">
                        <div class="inline-flex gap-2 leading-none px-1 pr-2 h-6 text-sm rounded-full items-center bg-green-100 text-green-500 cursor-default">
                            <IconSet name="success" /> {{ Math.round((project.requirements_all_tasks_complete_count / project.requirements_with_tasks_count) * 100) }}%
                        </div>
                    </Tooltip>

                    <Tooltip v-if="project.unknowns_count > 0" text="Unknowns">
                        <div class="inline-flex gap-2 leading-none px-1 pr-2 h-6 text-sm rounded-full items-center bg-yellow-100 text-yellow-600 cursor-default">
                            <IconSet name="question" /> {{ project.unknowns_count }}
                        </div>
                    </Tooltip>

                    <Tooltip v-if="project.blocked_requirements_count > 0" text="Blocked">
                        <div class="inline-flex gap-2 leading-none px-1 pr-2 h-6 text-sm rounded-full items-center bg-red-100 text-red-500 cursor-default">
                            <IconSet name="warning" /> {{ project.blocked_requirements_count }}
                        </div>
                    </Tooltip>
                </template>

                <SpinnerButton
                    v-if="project.can_restore"
                    type="button"
                    class="relative z-10 btn btn-primary-outline"
                    :loading="is_restoring"
                    icon="archive"
                    @click.stop="restore">

                    Restore
                </SpinnerButton>
            </div>

            <p v-if="!project.archived_at" class="text-sm text-gray-500 mt-2">Updated {{ updated_at }} ago</p>
        </div>
    </div>
</template>

<script>
import IconSet from '@/components/IconSet.vue';
import Project from '@/stores/models/Project';
import SpinnerButton from '@/components/SpinnerButton.vue';
import Tooltip from '@/components/Tooltip.vue';
import { formatDistance } from 'date-fns';
import { useAlertsStore } from '@/stores';

export default {
    components: {
        IconSet,
        SpinnerButton,
        Tooltip,
    },
    data() {
        return {
            is_restoring: false,
        };
    },
    computed: {
        archived_at() {
            return formatDistance(this.project.archived_at, new Date());
        },
        solo_mode() {
            return this.settings.mode === 'solo';
        },
        updated_at() {
            return formatDistance(this.project.updated_at, new Date());
        },
    },
    inject: ['api', 'settings'],
    methods: {
        restore() {
            this.is_restoring = true;

            this.api.post('projects/' + this.project.id + '/restore')
                .then((result) => {
                    Project.repository().save(result.data);

                    this.$router.push({ name: 'projects.show', params: {project_id: this.project.id}});

                    useAlertsStore().push('Project restored.');
                })
                .finally(() => this.is_restoring = false);
        },
    },
    props: [
        'project'
    ],
};
</script>
