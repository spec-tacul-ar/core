<template>
    <ModalLayout>
        <h2 class="text-4xl text-red-400 font-light mb-4">Delete requirement</h2>

        <form @submit.prevent="submit">
            <p class="mb-4">Deleting a requirement will delete all data associated with it including unknowns and tasks. Are you sure you want to do this?</p>

            <div v-if="has_children" class="mb-4">
                <FormInput type="checkbox" :id="elementId('confirmed')" label="I understand and wish to continue" v-model="form.confirmed" required />
            </div>

            <div class="d-flex justify-content-end align-items-center">
                <spinner-button type="submit" class="btn btn-danger" :disabled="!form.confirmed && this.has_children" :loading="is_waiting">Delete requirement</spinner-button>
            </div>
        </form>
    </ModalLayout>
</template>

<script>
import FormInput from '@core/components/forms/FormInput.vue';
import KeyboardShortcuts from '@core/mixins/KeyboardShortcuts';
import ModalLayout from '@core/components/layouts/ModalLayout.vue';
import SpinnerButton from '@core/components/SpinnerButton.vue';
import UniqueId from '@core/mixins/UniqueId';
import Requirement from '@core/stores/models/Requirement';
import { useAlertsStore } from '@core/stores';

export default {
    inject: ['api'],
    components: {
        FormInput,
        ModalLayout,
        SpinnerButton
    },
    computed: {
        has_children() {
            return this.requirement.has_tasks || this.requirement.has_unknowns;
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

            this.api.post('requirements/' + this.requirement.id + '/delete')
                .then(() => {
                    this.$emit('close');

                    Requirement.repository().delete(this.requirement.id);
                    useAlertsStore().push('Requirement deleted.');
                })
                .finally(() => this.is_waiting = false);
        }
    },
    mixins: [
        KeyboardShortcuts,
        UniqueId
    ],
    props: ['requirement'],
};
</script>
