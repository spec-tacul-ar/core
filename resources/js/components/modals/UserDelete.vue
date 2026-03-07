<template>
    <ModalLayout>
        <h2 class="text-4xl text-red-400 font-light mb-4">Delete user</h2>

        <form v-if="!has_requirements" @submit.prevent="submit">
            <p class="mb-4">Are you sure you want to do this?</p>

            <div class="d-flex justify-content-end align-items-center">
                <SpinnerButton type="submit" class="btn btn-danger" :loading="is_waiting">Delete user</SpinnerButton>
            </div>
        </form>

        <template v-if="has_requirements">
            <p class="mb-4">This user cannot be deleted while it is in use. It is currently used in the following requirements:</p>
            
            <ul class="list-disc italic ml-6">
                <li v-for="requirement in user.requirements" :key="requirement.id">
                    <RouterLink :to="{name: 'projects.show', params: {project_id: this.user.project_id }, hash: '#requirement_' + requirement.id}">
                        {{ requirement.name }}
                    </RouterLink>
                </li>
            </ul>
        </template>
    </ModalLayout>
</template>

<script>
import KeyboardShortcuts from '@core/mixins/KeyboardShortcuts';
import ModalLayout from '@core/components/layouts/ModalLayout.vue';
import SpinnerButton from '@core/components/SpinnerButton.vue';
import UniqueId from '@core/mixins/UniqueId';
import User from '@core/stores/models/User';
import { useAlertsStore } from '@core/stores';

export default {
    inject: ['api'],
    components: {
        ModalLayout,
        SpinnerButton
    },
    computed: {
        has_requirements() {
            return this.user.requirements.isNotEmpty();
        },
    },
    data() {
        return {
            'form': {
                'confirmed': false,
            },
            'is_waiting': false
        };
    },
    methods: {
        submit() {
            this.is_waiting = true;

            this.api.post('users/' + this.user.id + '/delete')
                .then(() => {
                    this.$emit('close');

                    User.repository().delete(this.user.id);
                    useAlertsStore().push('User deleted.');
                })
                .finally(() => this.is_waiting = false);
        }
    },
    mixins: [
        KeyboardShortcuts,
        UniqueId
    ],
    props: ['user'],
};
</script>
