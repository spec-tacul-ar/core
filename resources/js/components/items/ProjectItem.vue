<template>
    <div class="relative flex items-center flex-wrap gap-4 hover:bg-gray-100 transition-all duration-500 p-4">
        <div>
            <div><RouterLink :to="{name: 'projects.show', params: { project_id: project.id }}" class="text-xl font-semibold after:absolute after:inset-0">{{ project.name }}</RouterLink></div>
            <p class="text-sm">Updated {{ updated_at }} ago</p>
        </div>
        <div class="ml-auto">
            <div class="flex gap-1">
                <template v-if="project.requirements_count">
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
            </div>
        </div>
    </div>
</template>

<script>
import IconSet from '@/components/IconSet.vue';
import { formatDistance } from 'date-fns';

export default {
    components: {
        IconSet,
    },
    computed: {
        updated_at() {
            return formatDistance(this.project.updated_at, new Date());
        },
    },
    props: [
        'project'
    ],
};
</script>
