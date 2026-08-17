<template>
    <li class="flex gap-4 items-center bg-white px-4 py-2 dark:bg-gray-900">
        <div class="mr-auto">
            {{ task.name }}
        </div>

        <span v-if="task.is_complete" class="flex items-center gap-1 text-green-400 text-nowrap dark:text-green-500">
            <IconSet name="check" />
            {{ $t('Complete', project.locale) }}
        </span>

        <DropdownMenu v-if="project.can_write" ref="dropdown">
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
import DropdownMenu from '@/components/DropdownMenu.vue';
import DropdownMenuItem from '@/components/DropdownMenuItem.vue';
import { useAlertsStore } from '@/stores';
import IconSet from '@/components/IconSet.vue';
import Task from '@/stores/models/Task';

export default {
    inject: ['api', 'project'],
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

            this.api.post('tasks/' + this.task.id + '/toggle', {
                'is_complete': !this.task.is_complete
            })
                .then((result) => {
                    Task.repository().save(result.data);

                    useAlertsStore().push('Task updated.');

                    this.$refs.dropdown.close();
                })
                .finally(() => this.is_waiting_for_complete = false);
        },
        remove() {
            this.is_waiting_for_remove = true;

            this.api.post('tasks/' + this.task.id + '/delete')
                .then(() => {
                    this.$nextTick(function () {
                        Task.repository().delete(this.task.id);
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
