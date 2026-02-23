<template>
    <ModalLayout>
        <h2 class="text-4xl font-light mb-4">Resolve unknown</h2>

        <form @submit.prevent="submit">
            <p class="mb-4">Have you addressed the ambiguity in the requirement's description?</p>
            <p class="mb-4">You can optionally append text to the requirement's description using the field below.</p>

            <div class="mb-4">
                <FormInput type="textarea" :id="elementId('clarification')" label="Clarification" v-model="form.clarification" />
            </div>

            <SpinnerButton type="submit" class="btn btn-primary" :loading="is_waiting">Resolve unknown</SpinnerButton>
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
import Unknown from '@core/stores/models/Unknown';
import { useAlertsStore } from '@core/stores';

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
                'clarification': '',
            },
            'is_waiting': false,
        };
    },
    methods: {
        submit() {
            this.is_waiting = true;

            this.api.post('unknowns/' + this.unknown.id + '/delete')
                .then(() => {
                    if (this.form.clarification) {
                        let description = this.unknown.requirement.description;

                        if (description.length > 0) {
                            description = description + '\n\n';
                        }

                        description = description + this.form.clarification;

                        this.api.post('requirements/' + this.unknown.requirement_id + '/edit', { description })
                            .then((result) => Requirement.repository().save(result.data));
                    }

                    this.$emit('close');

                    Unknown.repository().delete(this.unknown.id);
                    useAlertsStore().push('Unknown resolved.');
                })
                .finally(() => this.is_waiting = false);
        }
    },
    mixins: [
        KeyboardShortcuts,
        UniqueId
    ],
    props: ['unknown'],
};
</script>
