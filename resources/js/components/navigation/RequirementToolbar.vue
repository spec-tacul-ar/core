<template>
    <div class="flex items-center print:hidden">
        <RouterLink :to="{ name: 'projects.requirements.edit', params: { requirement_id: requirement.id }}" class="p-2">
            <IconSet name="edit" />
        </RouterLink>

        <DropdownMenu>
            <DropdownMenuItem v-if="requirement.has_tasks && !requirement.is_complete" type="button" :loading="is_waiting_for_complete" icon="check-all" @click.stop="complete()">Complete all tasks</DropdownMenuItem>
            <DropdownMenuItem v-if="requirement.is_blocked" type="button" :loading="is_waiting_for_unblock" icon="unblock" @click.stop="unblock()">Unblock</DropdownMenuItem>

            <slot name="menu" />
            
            <DropdownMenuItem @click="openRequirementDeleteModal" icon="trash" class="dropdown-item" danger>Delete</DropdownMenuItem>
        </DropdownMenu>
    </div>
</template>

<script>
import DropdownMenu from '@core/components/DropdownMenu.vue';
import DropdownMenuItem from '@core/components/DropdownMenuItem.vue';
import RequirementDelete from '@core/components/modals/RequirementDelete.vue';
import IconSet from '@core/components/IconSet.vue';
import Requirement from '@core/stores/models/Requirement';
import Task from '@core/stores/models/Task';
import { useAlertsStore, useModalStore } from '@core/stores';

export default {
    inject: ['api'],
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
    data() {
        return {
            'is_waiting_for_complete': false,
            'is_waiting_for_unblock': false
        };
    },
    methods: {
        complete() {
            this.is_waiting_for_complete = true;

            this.api.post('requirements/' + this.requirement.id + '/tasks/complete')
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

            this.api.post('requirements/' + this.requirement.id + '/edit', {blocked_reason: null})
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
