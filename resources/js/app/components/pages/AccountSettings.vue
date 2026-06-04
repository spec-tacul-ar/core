<template>
    <DefaultLayout>
        <template v-slot:menu>
            <div class="w-full flex items-center gap-2">
                <RouterLink :to="{ name: 'projects.index' }" class="link flex items-center gap-2">
                    <IconSet name="chevron-left" />

                    Dashboard
                </RouterLink>

                <Tooltip text="Delete account" class="ml-auto">
                    <RouterLink :to="{ name: 'account.delete' }" class="p-2 text-red-400">
                        <IconSet name="trash" />
                    </RouterLink>
                </Tooltip>
            </div>
        </template>

        <div class="w-full max-w-sm mx-auto">
            <h1 class="font-display font-semibold text-4xl text-center mb-4 mx-6">Settings</h1>

            <Card class="p-6">
                <form @submit.prevent="submit">
                    <dl class="mb-3">
                        <dt class="font-bold mb-1 mr-auto">Email address</dt>
                        <dd>{{ email }}</dd>
                    </dl>

                    <div class="mb-3">
                        <FormInput type="text" id="name" label="Name" :error="errors.name" v-model="form.name" required />
                    </div>
                    
                    <spinner-button type="submit" class="btn btn-primary w-full" :loading="is_waiting">Save</spinner-button>
                </form>
            </Card>
        </div>
    </DefaultLayout>
</template>

<script>
import Card from '@/components/Card.vue';
import DefaultLayout from '@/components/layouts/DefaultLayout.vue';
import FormInput from '@/components/forms/FormInput.vue';
import IconSet from '@/components/IconSet.vue';
import SpinnerButton from '@/components/SpinnerButton.vue';
import Tooltip from '@/components/Tooltip.vue';
import TrackDirty from '@/mixins/TrackDirty';
import { useAlertsStore } from '@/stores';
import { useAuthStore } from '@/stores';

export default {
    beforeRouteLeave() {
        return !this.is_dirty || this.confirmClose();
    },
    components: {
        Card,
        DefaultLayout,
        FormInput,
        IconSet,
        SpinnerButton,
        Tooltip,
    },
    computed: {
        account: () => useAuthStore().account,
        mode_icon() {
            return {
                auto: 'mode-auto',
                light: 'mode-light',
                dark: 'mode-dark',
            }[this.mode];
        },
        mode_text() {
            return {
                auto: 'Auto',
                light: 'Light',
                dark: 'Dark',
            }[this.mode];
        },
    },
    data() {
        const auth_store = useAuthStore();

        return {
            email: auth_store.account.email,
            form: {
                name: auth_store.account.name,
            },
            errors: {},
            is_sending_verification: false,
            is_verification_sent: false,
            is_waiting: false,
            mode: 'auto',
        };
    },
    inject: [
        'api',
    ],
    methods: {
        nextMode() {
            const modes = ['auto', 'light', 'dark'];
            const index = modes.indexOf(this.mode);

            this.mode = modes[(index + 1) % modes.length];
        },
        sendVerificationEmail() {
            this.is_sending_verification = true;

            this.api.post('email/verification-notification')
                .then(() => {
                    useAlertsStore().push('Verification email sent.');

                    this.is_verification_sent = true;
                })
                .finally(() => this.is_sending_verification = false);
        },
        submit() {
            this.errors = {};
            this.is_waiting = true;

            this.api.post('account/edit', this.form)
                .then((result) => {
                    useAuthStore().account = result.data;
                    useAlertsStore().push('Your account was updated.');

                    this.setCleanForm();
                })
                .catch(error => this.errors = error.body.errors ?? {})
                .finally(() => this.is_waiting = false);
        }
    },
    mixins: [
        TrackDirty,
    ],
};
</script>
