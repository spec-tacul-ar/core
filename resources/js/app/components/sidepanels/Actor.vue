<template>
    <SidepanelLayout help="/docs/users">
        <template #buttons>
            <div class="flex items-center gap-4">
                <SpinnerButton type="submit" class="btn btn-primary" :loading="is_waiting" @click="submit">Save</SpinnerButton>
                <FormInput v-if="is_creating" type="checkbox" :id="elementId('add_another')" label="Create another after saving" v-model="add_another" />
            </div>
        </template>

        <form @submit.prevent="submit">
            <h2 class="text-4xl font-light mb-4">
                {{ is_creating ? 'Create user' : 'Edit user' }}
            </h2>

            <p v-if="other_editor_names" class="mb-4 text-amber-600 dark:text-amber-300">
                Also editing this user: {{ other_editor_names }}.
            </p>

            <ValidationMessages :errors="errors" />

            <div class="mb-4">
                <FormInput type="text" :id="elementId('name')" label="Name" :error="errors.name" v-model="form.name" required>
                    <template v-slot:help>
                        <p class="font-semibold mb-4">A succinctly label for this kind of user. Plural is best.</p>
                        <p>Examples:</p>
                        <ul class="list-disc italic ml-6">
                            <li>Users</li>
                            <li>Members</li>
                            <li>Anonymous visitors</li>
                            <li>Employees</li>
                            <li>Editors</li>
                        </ul>
                    </template>
                </FormInput>
            </div>
            <div>
                <FormInput type="textarea" :id="elementId('summary')" label="Summary" rows="5" :error="errors.summary" v-model="form.summary">
                    <template v-slot:help>
                        <p class="font-semibold mb-4">Summarise the duties are of this user, how they interact with the system and their motivations.</p>
                        <p>Example:</p>
                        <p class="italic">An annonymous visitor is one who has not authenticated with the system. They will therefore not have access to the members only area but can still access a limited number of resources.</p>
                    </template>
                </FormInput>
            </div>
        </form>
    </SidepanelLayout>
</template>

<script>
import FormInput from '@/components/forms/FormInput.vue';
import KeyboardShortcuts from '@/mixins/KeyboardShortcuts';
import SidepanelLayout from '@/components/layouts/SidepanelLayout.vue';
import SpinnerButton from '@/components/SpinnerButton.vue';
import TrackDirty from '@/mixins/TrackDirty';
import UniqueId from '@/mixins/UniqueId';
import ValidationMessages from '@/components/ValidationMessages.vue';
import Actor from '@/stores/models/Actor';
import { useAlertsStore, useAuthStore } from '@/stores';
import { inject } from 'vue';

export default {
    inject: ['api', 'echo'],
    beforeRouteLeave() {
        return !this.is_dirty || this.confirmClose();
    },
    components: {
        FormInput,
        SidepanelLayout,
        SpinnerButton,
        ValidationMessages,
    },
    computed: {
        is_creating() {
            return !this.actor_id;
        },
        other_editors() {
            const account = useAuthStore().account;

            return this.editors.filter(editor => String(editor.id) !== String(account?.id));
        },
        other_editor_names() {
            return this.other_editors.map(editor => editor.name).join(', ');
        },
    },
    data() {
        return {
            'add_another': false,
            'form': {
                name: this.actor?.name,
                summary: this.actor?.summary,
            },
            'errors': {},
            'editors': [],
            'is_waiting': false,
        };
    },
    methods: {
        submit() {
            this.errors = {};
            this.is_waiting = true;

            const endpoint = this.is_creating ? 'actors' : 'actors/' + this.actor_id + '/edit';
            const data = this.is_creating ? {...this.form, 'project_id': this.project_id} : this.form;

            this.api.post(endpoint, data)
                .then((result) => {
                    const actor = result.data;
                    
                    Actor.repository().save(actor);

                    useAlertsStore().push('User saved.');

                    if (this.add_another) {
                        this.form.name = '';
                        this.form.summary = '';
                    }

                    this.setCleanForm();

                    if (!this.add_another) {
                        this.$router.push({ name: 'projects.show', params: { project_id: actor.project_id } });
                    }
                })
                .catch(error => this.errors = error.body.errors ?? {})
                .finally(() => this.is_waiting = false);
        }
    },
    mounted() {
        if (!this.actor_id) {
            return;
        }

        this.echo.join('actors.editing.' + this.actor_id)
            .here((users) => this.editors = users)
            .joining((user) => this.editors.push(user))
            .leaving((user) => this.editors = this.editors.filter(editor => String(editor.id) !== String(user.id)));
    },
    mixins: [
        KeyboardShortcuts,
        TrackDirty,
        UniqueId
    ],
    props: {
        'project_id': {
            'type': String,
            'required': true,
        },
        'actor_id': {
            'type': String,
            'required': false,
        },
    },
    unmounted() {
        if (this.actor_id) {
            this.echo.leave('actors.editing.' + this.actor_id);
        }
    },
    async setup(props) {
        if (!props.actor_id) {
            return;
        }

        const api = inject('api');
        const actors = Actor.repository();
        let actor = actors.find(props.actor_id);

        if (!actor) {
            const result = await api.get('actors/' + props.actor_id);
            actor = Actor.repository().save(result.data);
        }

        return {
            actor,
        };
    },
};
</script>
