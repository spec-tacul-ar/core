<template>
    <ModalLayout>
        <form @submit.prevent="submit">
            <h2 class="text-4xl text-red-400 dark:text-red-300 font-light mb-4">Delete project</h2>

            <p class="mb-4">Deleting a project will delete all data associated with it including all requirements. This is not reversible. Are you sure you want to do this?</p>

            <div class="mb-4">
                <FormInput type="checkbox" :id="elementId('confirmed')" label="I understand and wish to continue" v-model="form.confirmed" required />
            </div>

            <spinner-button type="submit" class="btn btn-danger" :disabled="!form.confirmed" :loading="is_waiting">Delete project</spinner-button>
        </form>
    </ModalLayout>
</template>

<script>
import FormInput from '@/components/forms/FormInput.vue';
import KeyboardShortcuts from '@/mixins/KeyboardShortcuts';
import ModalLayout from '@/components/layouts/ModalLayout.vue';
import SpinnerButton from '@/components/SpinnerButton.vue';
import UniqueId from '@/mixins/UniqueId';
import Project from '@/stores/models/Project';
import { useAlertsStore } from '@/stores';

export default {
    inject: ['api'],
    components: {
        FormInput,
        ModalLayout,
        SpinnerButton
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

            this.api.post('projects/' + this.project.id + '/delete')
                .then(() => {
                    this.$emit('close');

                    this.$router.push({ name: 'projects.index' })
                        .then(() => {
                            Project.repository().delete(this.project.id);
                            useAlertsStore().push('Project deleted.');
                        });
                })
                .finally(() => this.is_waiting = false);
        }
    },
    mixins: [
        KeyboardShortcuts,
        UniqueId
    ],
    mounted() {
        document.title = 'Delete project';
    },
    props: ['project'],
};
</script>
