<template>
    <SidepanelLayout help="/docs/feedback">
        <template #toolbar>
            <RouterLink v-if="commentable" :to="{ name: 'projects.feedback' }" title="Feedback" class="flex items-center gap-2 p-2 -ml-2 text-indigo-600 mr-auto">
                <IconSet name="chevron-left" />
                All feedback
            </RouterLink>

            <button v-if="commentable" type="button" class="hidden sm:block p-2" @click="scrollToCommentable()">
                <IconSet name="scroll-to" class="size-6" />
            </button>

            <Tooltip v-if="has_unread && !commentable" text="Mark as read" @click="markAsRead()">
                <button type="button" class="p-2">
                    <IconSet name="mark-read" class="size-6" />
                </button>
            </Tooltip>
        </template>

        <h2 v-if="commentable" class="text-4xl font-light mb-4">Re: <span class="italic">{{ commentable_name }}</span></h2>
        <h2 v-else class="text-4xl font-light mb-4">All feedback</h2>

        <LoadingSpinner label="Loading comments" v-if="this.comments.isEmpty() && is_loading" />

        <form v-if="project.can_comment" @submit.prevent="submit" class="flex items-end gap-2 mb-4">
            <div class="grow">
                <FormInput type="textarea" label="Message" rows="3" :id="elementId('message')" v-model="form.message" />
            </div>

            <SpinnerButton type="submit" class="btn btn-primary ml-auto p-3" :loading="is_saving">
                <IconSet name="send" />
            </SpinnerButton>
        </form>

        <div class="space-y-4">
            <CommentItem v-for="comment in comments" :key="comment.id" :comment="comment">
                <div v-if="!commentable && comment.commentable_type === 'requirement'" class="text-sm mb-2">
                    Re: <RouterLink :to="{ name: 'projects.requirements.feedback', params: { project_id: project.id, requirement_id: comment.commentable_id }}" class="underline">{{ comment.commentable_name }}</RouterLink>
                </div>
                <div v-if="!commentable && comment.commentable_type === 'feature'" class="text-sm mb-2">
                    Re: <RouterLink :to="{ name: 'projects.features.feedback', params: { project_id: project.id, feature_id: comment.commentable_id }}" class="underline">{{ comment.commentable_name }}</RouterLink>
                </div>
            </CommentItem>
        </div>

        <p v-if="comments.isEmpty()" class="text-center text-gray-400">No comments.</p>
    </SidepanelLayout>
</template>

<script>
import CommentItem from '@/components/items/CommentItem.vue';
import Comment from '@/stores/models/Comment';
import Project from '@/stores/models/Project';
import LoadingSpinner from '@/components/LoadingSpinner.vue';
import KeyboardShortcuts from '@/mixins/KeyboardShortcuts';
import SidepanelLayout from '@/components/layouts/SidepanelLayout.vue';
import UniqueId from '@/mixins/UniqueId';
import FormInput from '@/components/forms/FormInput.vue';
import IconSet from '@/components/IconSet.vue';
import SpinnerButton from '@/components/SpinnerButton.vue';
import Tooltip from '@/components/Tooltip.vue';
import TrackDirty from '@/mixins/TrackDirty';
import { isAfter } from 'date-fns';
import { useAlertsStore } from '@/stores';

export default {
    beforeRouteLeave() {
        return !this.is_dirty || this.confirmClose();
    },
    components: {
        CommentItem,
        LoadingSpinner,
        SidepanelLayout,
        FormInput,
        IconSet,
        SpinnerButton,
        Tooltip,
    },
    computed: {
        comments() {
            const comments = this.project.comments.sortByDesc('created_at');

            if (this.commentable_type) {
                return comments
                    .where('commentable_type', this.commentable_type)
                    .where('commentable_id', this.commentable.id);
            }

            return comments;
        },
        commentable() {
            if (this.feature_id) {
                return this.project.features.first(item => item.id === this.feature_id);
            }

            if (this.requirement_id) {
                return this.project.features
                    .flatMap(feature => feature.requirements.toArray())
                    .first(item => item.id === this.requirement_id);
            }

            return null;
        },
        commentable_type() {
            if (this.feature_id) {
                return 'feature';
            }

            if (this.requirement_id) {
                return 'requirement';
            }

            return null;
        },
        commentable_name() {
            const name = this.commentable.name;

            if (this.commentable_type === 'requirement') {
                return name.charAt(0).toUpperCase() + name.slice(1);
            }

            return name;
        },
        has_unread() {
            return this.project.comments.contains(comment => isAfter(comment.created_at,this.project.readmark));
        },
    },
    data() {
        return {
            form: {
                message: '',
            },
            is_loading: false,
            is_saving: false,
        };
    },
    inject: [
        'api',
    ],
    methods: {
        markAsRead() {
            this.api.post('projects/' + this.project_id + '/readmark')
                .then(result => {
                    Project.repository().save(result.data);

                    useAlertsStore().push('All feedback marked as read.');
                });
        },
        scrollToCommentable() {
            document.getElementById(this.commentable_type + '_' + this.commentable.id).scrollIntoView(true);
        },
        submit() {
            this.errors = {};
            this.is_saving = true;

            const data = {
                ...this.form,
                'project_id': this.project_id,
            };

            if (this.commentable) {
                data.commentable_type = this.commentable_type;
                data.commentable_id = this.commentable.id;
            }

            this.api.post('comments', data)
                .then(result => {
                    Comment.repository().save(result.data);

                    useAlertsStore().push('Feedback saved.');

                    this.form.message = '';

                    this.setCleanForm();
                })
                .catch(error => this.errors = error.body.errors ?? {})
                .finally(() => this.is_saving = false);
        },
    },
    mixins: [
        KeyboardShortcuts,
        TrackDirty,
        UniqueId
    ],
    mounted() {
        this.api.get('comments', { query: { project_id: this.project_id } })
            .then((result) => Comment.repository().saveMany(result.data))
            .finally(() => this.is_loading = false);
    },
    props: {
        'project_id': {
            'type': String,
            'required': true,
        },
        'requirement_id': String,
        'feature_id': String,
    },
    async setup(props) {
        return {
            project: Project.repository().find(props.project_id),
        };
    },
};
</script>
