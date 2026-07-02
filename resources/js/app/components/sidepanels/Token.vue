<template>
    <SidepanelLayout>
        <h2 class="text-4xl font-light mb-4">Create token</h2>

        <form v-if="!secret" @submit.prevent="submit">
            <ValidationMessages :errors="errors" />

            <div class="mb-4">
                <FormInput type="text" :id="elementId('name')" label="Name" :error="errors.name" v-model="form.name" required>
                    <template v-slot:help>
                        <p class="font-semibold mb-4">A label for the token so you know where it is being used.</p>
                        <p>Examples:</p>
                        <ul class="list-disc italic ml-6">
                            <li>Cursor</li>
                            <li>ChatGPT</li>
                        </ul>
                    </template>
                </FormInput>
            </div>

            <div class="flex items-center gap-4">
                <SpinnerButton type="submit" class="btn btn-primary" :loading="is_waiting" @click="submit">Create</SpinnerButton>
            </div>
        </form>

        <section v-if="secret">
            <p class="mb-4">Here is your new token. Be sure to copy it now as it will not be accessible again.</p>

            <div class="border border-gray-200 p-4 mb-4 dark:border-gray-800 dark:bg-gray-900">
                <code class="font-mono break-all overflow-auto text-xs">{{ secret }}</code>
            </div>

            <button v-if="can_copy" type="button" class="btn btn-primary-outline" @click="copy">
                <IconSet name="copy" /> {{ is_copied ? 'Copied to clipboard' : 'Copy to clipboard' }}
            </button>

            <button v-if="!can_copy" type="button" class="btn btn-primary" @click="$emit('close')">Done</button>
        </section>
    </SidepanelLayout>
</template>

<script>
import FormInput from '@/components/forms/FormInput.vue';
import IconSet from '@/components/IconSet.vue';
import KeyboardShortcuts from '@/mixins/KeyboardShortcuts';
import SidepanelLayout from '@/components/layouts/SidepanelLayout.vue';
import SpinnerButton from '@/components/SpinnerButton.vue';
import TrackDirty from '@/mixins/TrackDirty';
import UniqueId from '@/mixins/UniqueId';
import ValidationMessages from '@/components/ValidationMessages.vue';
import Token from '@/stores/models/Token';
import { useAlertsStore } from '@/stores';

export default {
    inject: ['api'],
    beforeRouteLeave() {
        return !this.is_dirty || this.confirmClose();
    },
    components: {
        FormInput,
        IconSet,
        SidepanelLayout,
        SpinnerButton,
        ValidationMessages,
    },
    computed: {
        can_copy() {
            return !!navigator.clipboard?.writeText;
        },
    },
    data() {
        return {
            errors: {},
            form: {
                name: '',
            },
            is_copied: false,
            is_waiting: false,
            token: null,
            secret: null,
        };
    },
    methods: {
        async copy() {
            await navigator.clipboard.writeText(this.secret);
            this.is_copied = true;
        },
        submit() {
            this.errors = {};
            this.is_waiting = true;

            this.api.post('tokens', this.form)
                .then((result) => {
                    const token = result.data;

                    this.secret = token.secret;

                    delete token.secret;

                    Token.repository().save(token);

                    useAlertsStore().push('Token created.');

                    this.setCleanForm();
                })
                .catch(error => this.errors = error.body.errors ?? {})
                .finally(() => this.is_waiting = false);
        },
    },
    mixins: [
        KeyboardShortcuts,
        TrackDirty,
        UniqueId
    ],
};
</script>
