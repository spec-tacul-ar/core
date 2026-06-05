<template>
    <div class="flex items-center print:hidden">
        <RouterLink v-if="project.can_write" :to="{ name: 'projects.requirements.edit', params: { requirement_id: requirement.id }}" class="p-2">
            <IconSet name="edit" />
        </RouterLink>

        <DropdownMenu>
            <DropdownMenuItem v-if="project.can_write && requirement.has_tasks && !requirement.is_complete" type="button" :loading="is_waiting_for_complete" icon="check-all" @click.stop="complete()">Complete all tasks</DropdownMenuItem>
            <DropdownMenuItem v-if="project.can_write && requirement.is_blocked" type="button" :loading="is_waiting_for_unblock" icon="unblock" @click.stop="unblock()">Unblock</DropdownMenuItem>
            <DropdownMenuItem :to="{ name: 'projects.requirements.feedback', params: { requirement_id: requirement.id }}" icon="feedback">Feedback</DropdownMenuItem>
                        
            <slot name="menu" />
            
            <DropdownMenuItem v-if="project.can_write" @click="openRequirementDeleteModal" icon="trash" class="dropdown-item" danger>Delete</DropdownMenuItem>
        </DropdownMenu>
    </div>
</template>

<script>
import DropdownMenu from '@/components/DropdownMenu.vue';
import DropdownMenuItem from '@/components/DropdownMenuItem.vue';
import RequirementDelete from '@/components/modals/RequirementDelete.vue';
import IconSet from '@/components/IconSet.vue';
import Requirement from '@/stores/models/Requirement';
import Task from '@/stores/models/Task';
import { useAlertsStore, useModalStore } from '@/stores';

export default {
    inject: ['api', 'project'],
    components: {
        DropdownMenu,
        DropdownMenuItem,
        IconSet,
    },
    data() {
        return {
            'is_waiting_for_complete': false,
            'is_waiting_for_unblock': false
        };
    },
    methods: {
        complete() {
            this.is_waiting_for_complete = true;

            this.api.post('requirements/' + this.requirement.id + '/complete')
                .then((result) => {
                    Task.repository().saveMany(result.data);

                    useAlertsStore().push('Requirement completed.');
                })
                .finally(() => this.is_waiting_for_complete = false);
        },
        openRequirementDeleteModal() {
            useModalStore().open(RequirementDelete, {requirement: this.requirement});
        },
        unblock() {
            this.is_waiting_for_unblock = true;

            this.api.post('requirements/' + this.requirement.id + '/unblock')
                .then((result) => {
                    Requirement.repository().save(result.data);

                    useAlertsStore().push('Requirement unblocked.');
                })
                .finally(() => this.is_waiting_for_unblock = false);
        }
    },
    props: ['requirement'],
};
</script>
