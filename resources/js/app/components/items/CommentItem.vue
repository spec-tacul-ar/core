<template>
    <div
        :class="[
            was_me ? 'ml-12' : 'mr-12',
            is_deleting ? 'opacity-50 pointer-events-none' : '',
        ]">

        <div
            class="block bg-gray-100 p-4 rounded-xl mb-1 dark:bg-gray-800"
            :class="is_unread && !was_me ? 'outline-2 outline-offset-3' : ''">

            <button
                type="button"
                class="block float-right p-2 -mr-2 -mt-2"
                :class="!is_long || is_expanded ? 'invisible' : ''"
                @click.prevent="expand">

                <IconSet name="chevron-down" />
            </button>

            <slot />

            <div :class="!is_expanded && is_long ? 'line-clamp-2' : ''">{{ comment.message }}</div>
        </div>
        
        <div class="text-sm text-gray-600 ml-4 dark:text-gray-400">
            {{ author }} - {{ date }}

            <template v-if="was_me && comment.project.can_comment"> - <button type="button" class="text-red-400 dark:text-red-300" @click="remove">Delete</button></template>
        </div>
    </div>
</template>

<script>
import Comment from '@/stores/models/Comment';
import { format, isBefore } from 'date-fns';
import { useAlertsStore } from '@/stores';
import { useAuthStore } from '@/stores';
import IconSet from '@/components/IconSet.vue';

// TODO Accurately determine the number of lines and decide if we show the expand button.

export default {
    components: {
        IconSet,
    },
    computed: {
        author() {
            return this.was_me ? 'You' : this.comment.account_name;
        },
        date() {
            const date = Date.parse(this.comment.created_at);

            return format(date, 'Pp');
        },
        href() {
            return '#' + this.comment.commentable_type + '_' + this.comment.commentable_id;
        },
        is_long() {
            return this.comment.message.length > 150;
        },
        is_unread() {
            return this.comment.project.readmark && isBefore(this.comment.project.readmark, this.comment.created_at);
        },
        was_me() {
            return this.comment.account_id === useAuthStore().account.id;
        },
    },
    data() {
        return {
            is_deleting: false,
            is_expanded: false,
        };
    },
    inject: [
        'api',
    ],
    methods: {
        remove() {
            if (confirm('Are you sure you want to delete this comment?')) {
                this.is_deleting = true;

                this.api.post('comments/' + this.comment.id + '/delete')
                    .then(() => {
                        Comment.repository().delete(this.comment.id);
                        useAlertsStore().push('Comment deleted.');
                    })
                    .finally(() => this.is_deleting = false);
            }
        },
        expand() {
            this.is_expanded = true;
        },
    },
    props: {
        'comment': Object,
    },
};
</script>
