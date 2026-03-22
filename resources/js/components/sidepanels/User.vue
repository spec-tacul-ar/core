<template>
    <SidepanelLayout>
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
import User from '@/stores/models/User';
import { useAlertsStore } from '@/stores';
import { inject } from 'vue';

export default {
    inject: ['api'],
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
            return !this.user_id;
        },
    },
    data() {
        return {
            'add_another': false,
            'form': {
                name: this.user?.name,
                summary: this.user?.summary,
            },
            'errors': {},
            'is_waiting': false,
        };
    },
    methods: {
        submit() {
            this.errors = {};
            this.is_waiting = true;

            const endpoint = this.is_creating ? 'users/add' : 'users/' + this.user_id + '/edit';
            const data = this.is_creating ? {...this.form, 'project_id': this.project_id} : this.form;

            this.api.post(endpoint, data)
                .then((result) => {
                    const user = result.data;
                    
                    User.repository().save(user);

                    useAlertsStore().push('User saved.');

                    if (this.add_another) {
                        this.form.name = '';
                        this.form.summary = '';
                    }

                    this.setCleanForm();

                    if (!this.add_another) {
                        this.$router.push({ name: 'projects.show', params: { project_id: user.project_id } });
                    }
                })
                .catch(error => this.errors = error.body.errors ?? {})
                .finally(() => this.is_waiting = false);
        }
    },
    mixins: [
        KeyboardShortcuts,
        TrackDirty,
        UniqueId
    ],
    props: {
        'project_id': {
            'type': Number,
            'required': true,
        },
        'user_id': {
            'type': Number,
            'required': false,
        },
    },
    async setup(props) {
        if (!props.user_id) {
            return;
        }

        const api = inject('api');
        const users = User.repository();
        let user = users.find(props.user_id);

        if (!user) {
            const result = await api.get('users/' + props.user_id + '/read');
            user = User.repository().save(result.data);
        }

        return {
            user,
        };
    },
};
</script>
