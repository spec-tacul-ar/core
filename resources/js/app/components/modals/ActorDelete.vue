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
                <li v-for="requirement in actor.requirements" :key="requirement.id">
                    <RouterLink :to="{name: 'projects.show', params: {project_id: this.actor.project_id }, hash: '#requirement_' + requirement.id}">
                        {{ requirement.name }}
                    </RouterLink>
                </li>
            </ul>
        </template>
    </ModalLayout>
</template>

<script>
import KeyboardShortcuts from '@/mixins/KeyboardShortcuts';
import ModalLayout from '@/components/layouts/ModalLayout.vue';
import SpinnerButton from '@/components/SpinnerButton.vue';
import UniqueId from '@/mixins/UniqueId';
import Actor from '@/stores/models/Actor';
import { useAlertsStore } from '@/stores';

export default {
    inject: ['api'],
    components: {
        ModalLayout,
        SpinnerButton
    },
    computed: {
        has_requirements() {
            return this.actor.requirements.isNotEmpty();
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

            this.api.post('actors/' + this.actor.id + '/delete')
                .then(() => {
                    this.$emit('close');

                    Actor.repository().delete(this.actor.id);
                    useAlertsStore().push('User deleted.');
                })
                .finally(() => this.is_waiting = false);
        }
    },
    mixins: [
        KeyboardShortcuts,
        UniqueId
    ],
    props: ['actor'],
};
</script>
