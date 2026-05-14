<template>
    <SidepanelLayout help="/docs/team">
        <template #toolbar>
            <Tooltip v-if="can_leave" text="Leave project">
                <button type="button" class="p-2" @click="leave">
                    <IconSet name="leave" class="text-red-400 size-6" />
                </button>
            </Tooltip>
        </template>

        <h2 class="text-4xl font-light mb-4">People</h2>

        <dl class="bg-gray-50 p-4 -mx-4 grid grid-cols-[max-content_1fr] gap-4 mb-4">
            <dt class="font-semibold">Owners</dt>
            <dd>Can do everything, including invite others and delete the project.</dd>

            <dt class="font-semibold">Editors</dt>
            <dd>Can view and edit projects.</dd>

            <dt class="font-semibold">Viewers</dt>
            <dd>Can only view projects and leave comments.</dd>
        </dl>

        <div class="divide-y divide-gray-200 mb-4">
            <div v-for="contributor in contributors" :key="contributor.id">
                <ContributorItem :contributor="contributor" />
            </div>
        </div>

        <section v-if="project.can_manage">
            <h3 class="text-3xl font-light mb-4">Invitations</h3>

            <form class="mb-4" @submit.prevent="submit()">
                <p class="mb-4">Share this project with other people so they can view it and give feedback.</p>

                <div class="mb-4">
                    <FormInput
                        type="email"
                        :id="elementId('email')"
                        label="Email address"
                        rows="3"
                        :error="errors.email"
                        v-model="form.email"
                        required />
                </div>

                <div class="mb-4">
                    <FormOptions
                        type="radio"
                        :options="roles"
                        :id="elementId('role')"
                        label="Role"
                        :error="errors.role"
                        v-model="form.role"
                        required />
                </div>

                <button type="submit" class="btn btn-primary mt-2">Invite</button>
            </form>

            <div class="divide-y divide-gray-200">
                <div v-for="invitation in invitations" :key="invitation.id" class="flex items-center gap-2 py-4">
                    <div class="mr-auto">
                        <div class="flex gap-4 items-baseline">
                            <p class="font-semibold mr-auto">{{ invitation.email }}</p>
                            <span class="border bg-gray-200 text-sm rounded leading-none px-2 py-1">{{ invitation.role_name }}</span>
                        </div>

                        <p class="text-sm text-gray-400">Invited by {{ invitation.account_name }}</p>
                    </div>

                    <button type="button" class="btn btn-sm btn-danger-outline" @click="removeInvitation(invitation)"><IconSet name="x-lg" class="size-4" /></button>
                </div>
            </div>
        </section>
    </SidepanelLayout>
</template>

<script>
import ContributorItem from '@/components/items/ContributorItem.vue';
import Invitation from '@/stores/models/Invitation';
import Project from '@/stores/models/Project';
import FormInput from '@/components/forms/FormInput.vue';
import FormOptions from '@/components/forms/FormOptions.vue';
import IconSet from '@/components/IconSet.vue';
import KeyboardShortcuts from '@/mixins/KeyboardShortcuts';
import Model from '@/stores/Model.js';
import SidepanelLayout from '@/components/layouts/SidepanelLayout.vue';
import Tooltip from '@/components/Tooltip.vue';
import TrackDirty from '@/mixins/TrackDirty';
import UniqueId from '@/mixins/UniqueId';
import { useAlertsStore, useAuthStore } from '@/stores';

export default {
    components: {
        ContributorItem,
        FormInput,
        FormOptions,
        IconSet,
        SidepanelLayout,
        Tooltip,
    },
    computed: {
        active_invitations() {
            return this.project.invitations.whereNotNull('account').sortByDesc('created_at');
        },
        can_leave() {
            const me = this.project.contributors.firstWhere('account_id', useAuthStore().account.id);

            if (me.role === 'owner') {
                return this.project.contributors.where('role', 'owner').count() > 1;
            }

            return true;
        },
        pending_invitations() {
            return this.project.invitations.whereNull('account').sortByDesc('created_at');
        },
        contributors() {
            const order = { owner: 1, editor: 2, viewer: 3 };

            return this.project.contributors
                .sortBy('created_at')
                .sortBy(contributor => order[contributor.role.value]);
        },
        invitations() {
            return this.project.invitations
                .sortBy('email');
        },
    },
    data() {
        return {
            errors: {},
            form: {
                email: null,
                role: null,
            },
            is_saving: false,
            is_removing: false,
            roles: [
                {
                    id: 'owner',
                    name: 'Owner',
                }, {
                    id: 'editor',
                    name: 'Editor',
                }, {
                    id: 'viewer',
                    name: 'Viewer',
                },
            ],
        };
    },
    inject: [
        'api',
    ],
    methods: {
        leave() {
            if (!confirm('Are you sure you wish to leave this project entirely?')) {
                return;
            }

            const me = this.project.contributors.firstWhere('account_id', useAuthStore().account.id);

            this.api.post('contributors/' + me.id + '/delete')
                .then(() => {
                    // TODO Is is even possible to do this without an error?
                    Model.resetRepositories();

                    this.$router.push({ name: 'projects.index' });

                    useAlertsStore().push('You have left the project.');
                })
                .catch(error => this.errors = error.body.errors ?? {});
        },
        removeInvitation(invitation) {
            if (!confirm('Are you sure?')) {
                return;
            }

            this.is_removing = true;

            this.api.post('invitations/' + invitation.id + '/delete')
                .then(() => {
                    Invitation.repository().delete(invitation.id);
                })
                .finally(() => this.is_removing = false);
        },
        submit() {
            this.errors = {};
            this.is_saving = true;

            this.api.post('invitations/add', {...this.form, 'project_id': this.project_id})
                .then((result) => {
                    const invitation = result.data;
                    
                    Invitation.repository().save(invitation);

                    useAlertsStore().push('Invitation sent.');

                    this.form.email = '';

                    this.setCleanForm();
                })
                .catch(error => this.errors = error.body.errors ?? {})
                .finally(() => this.is_saving = false);
        }
    },
    mixins: [
        KeyboardShortcuts,
        TrackDirty,
        UniqueId
    ],
    mounted() {
        if (this.project.can_manage) {
            this.api.get('invitations/browse', { query: { project_id: this.project_id } })
                .then((result) => Invitation.repository().saveMany(result.data))
                .finally(() => this.is_loading = false);
        }
    },
    props: {
        'project_id': {
            'type': String,
            'required': true
        }
    },
    async setup(props) {
        return {
            project: Project.repository().find(props.project_id),
        };
    },
};
</script>
