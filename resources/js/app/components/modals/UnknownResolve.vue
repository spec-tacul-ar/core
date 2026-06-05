<template>
    <ModalLayout>
        <h2 class="text-4xl font-light mb-4">Resolve unknown</h2>

        <form @submit.prevent="submit">
            <p class="mb-4">Have you addressed the ambiguity in the requirement's description?</p>
            <p class="mb-4">You can optionally append text to the requirement's description using the field below.</p>

            <div class="mb-4">
                <FormInput type="textarea" :id="elementId('text')" label="Clarification" v-model="form.text" />
            </div>

            <SpinnerButton type="submit" class="btn btn-primary" :loading="is_waiting">Resolve unknown</SpinnerButton>
        </form>
    </ModalLayout>
</template>

<script>
import FormInput from '@/components/forms/FormInput.vue';
import KeyboardShortcuts from '@/mixins/KeyboardShortcuts';
import ModalLayout from '@/components/layouts/ModalLayout.vue';
import SpinnerButton from '@/components/SpinnerButton.vue';
import UniqueId from '@/mixins/UniqueId';
import Requirement from '@/stores/models/Requirement';
import Unknown from '@/stores/models/Unknown';
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
                'text': '',
            },
            'is_waiting': false,
        };
    },
    methods: {
        submit() {
            this.is_waiting = true;

            this.api.post('unknowns/' + this.unknown.id + '/delete')
                .then(() => {
                    if (this.form.text) {
                        this.api.post('requirements/' + this.unknown.requirement_id + '/append', {
                            text: this.form.text,
                        })
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
