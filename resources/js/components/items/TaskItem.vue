<template>
    <li class="flex gap-4 items-center bg-white px-4 py-2">
        <div class="mr-auto">
            {{ task.name }}

            <span v-if="task.estimate !== null" class="bg-gray-400 text-white text-xs px-2 py-1 ml-2">{{ task.estimate }} {{ task.estimate == 1 ? 'hour' : 'hours' }}</span>
        </div>

        <span v-if="task.is_complete" class="flex items-center gap-1 text-green-400 text-nowrap">
            <IconSet name="check" />
            Complete
        </span>

        <DropdownMenu ref="dropdown">
            <DropdownMenuItem
                :icon="!task.is_complete ? 'check-lg' : 'x-lg'"
                :loading="is_waiting_for_complete"
                @click.stop="toggleComplete()">

                Mark as {{ task.is_complete ? 'incomplete' : 'complete' }}
            </DropdownMenuItem>

            <DropdownMenuItem
                icon="trash"
                :loading="is_waiting_for_remove"
                @click.stop="remove()"
                danger>

                Delete
            </DropdownMenuItem>
        </DropdownMenu>
    </li>
</template>

<script>
import DropdownMenu from '@core/components/DropdownMenu.vue';
import DropdownMenuItem from '@core/components/DropdownMenuItem.vue';
import { useAlertsStore, useTasksStore } from '@core/stores';
import IconSet from '@core/components/IconSet.vue';

export default {
    components: {
        IconSet,
        DropdownMenu,
        DropdownMenuItem,
    },
    data() {
        return {
            'is_waiting_for_complete': false,
            'is_waiting_for_remove': false
        };
    },
    methods: {
        toggleComplete() {
            this.is_waiting_for_complete = true;

            this.$api.post('tasks/' + this.task.id + '/edit', {
                    'is_complete': !this.task.is_complete
                })
                .then((result) => {
                    useTasksStore().save(result.data);

                    useAlertsStore().push('Task updated.');

                    this.$refs.dropdown.close();
                })
                .finally(() => this.is_waiting_for_complete = false);
        },
        remove() {
            this.is_waiting_for_remove = true;

            this.$api.post('tasks/' + this.task.id + '/delete')
                .then((result) => {
                    this.$nextTick(function () {
                        useTasksStore().delete(this.task.id);
                    });

                    useAlertsStore().push('Task deleted.');
                })
                .finally(() => this.is_waiting_for_remove = false);
        },
    },
    props: [
        'task'
    ]
};
</script>
