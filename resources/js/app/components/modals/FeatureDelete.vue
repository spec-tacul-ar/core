<template>
    <ModalLayout>
        <h2 class="text-4xl text-red-400 dark:text-red-300 font-light mb-4">Delete feature</h2>

        <form v-if="!has_requirements" @submit.prevent="submit">
            <p class="mb-4">Deleting a feature will delete all data associated with it. Any comments on this feature will be deleted forever. Are you sure you want to do this?</p>

            <div class="d-flex justify-content-end align-items-center">
                <SpinnerButton type="submit" class="btn btn-danger" :loading="is_waiting">Delete feature</SpinnerButton>
            </div>
        </form>

        <template v-if="has_requirements">
            <p class="mb-4">This feature cannot be deleted while it is in use. It is currently used in the following requirements:</p>
            
            <ul class="list-disc italic ml-6">
                <li v-for="requirement in feature.requirements" :key="requirement.id">
                    <RouterLink :to="{name: 'projects.show', params: {project_id: this.feature.project_id }, hash: '#requirement_' + requirement.id}">
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
import Feature from '@/stores/models/Feature';
import { useAlertsStore } from '@/stores';

export default {
    inject: ['api'],
    components: {
        ModalLayout,
        SpinnerButton
    },
    computed: {
        has_requirements() {
            return this.feature.requirements.isNotEmpty();
        },
    },
    data() {
        return {
            'is_waiting': false
        };
    },
    methods: {
        submit() {
            this.is_waiting = true;

            this.api.post('features/' + this.feature.id + '/delete')
                .then(() => {
                    this.$emit('close');

                    Feature.repository().delete(this.feature.id);
                    useAlertsStore().push('Feature deleted.');
                })
                .finally(() => this.is_waiting = false);
        }
    },
    mixins: [
        KeyboardShortcuts,
        UniqueId
    ],
    props: ['feature'],
};
</script>
