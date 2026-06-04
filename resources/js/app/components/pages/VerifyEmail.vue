<template>
    <GuestLayout>
        <div class="w-full max-w-sm mx-auto">
            <h1 class="font-display font-semibold text-4xl text-center mb-4 mx-6">Verify your email</h1>

            <Card class="p-6">
                <p class="mb-4">Please check your inbox for a link to verify your email address.</p>

                <div class="flex gap-2">
                    <SpinnerButton
                        type="button"
                        class="btn btn-primary w-full"
                        :loading="is_sending"
                        :disabled="is_cooling_down"
                        @click="send">

                        Resend Verification Email
                    </SpinnerButton>
                    
                    <button type="button" class="shrink-0 btn btn-primary-outline" @click="logout">Log out</button>
                </div>
            </Card>
        </div>
    </GuestLayout>
</template>

<script>
import Card from '@/components/Card.vue';
import GuestLayout from '@/components/layouts/GuestLayout.vue';
import SpinnerButton from '@/components/SpinnerButton.vue';
import { useAlertsStore, useAuthStore } from '@/stores';

export default {
    components: {
        Card,
        GuestLayout,
        SpinnerButton,
    },
    computed: {
        account() {
            return useAuthStore().account;
        },
    },
    data() {
        return {
            is_sending: false,
            is_cooling_down: true,
            timer: null,
        };
    },
    inject: ['api'],
    methods: {
        send() {
            this.is_sending = true;

            this.api.client().post('email/verification-notification')
                .then(() => {
                    useAlertsStore().push('Verification email sent.');

                    this.cooldown();
                })
                .finally(() => this.is_sending = false);
        },
        cooldown() {
            this.is_cooling_down = true;

            this.timer = window.setTimeout(() => {
                this.is_cooling_down = false;
            }, 5000);
        },
        async logout() {
            await this.api.client().post('auth/logout');

            useAuthStore().resume_session = false;
            useAuthStore().status = null;

            this.$router.push({ name: 'auth.login' });
            
            useAlertsStore().push('You have been logged out.');
        },
    },
    mounted() {
        this.cooldown();
    },
    beforeUnmount() {
        if (this.verification_cooldown_timer) {
            window.clearTimeout(this.verification_cooldown_timer);
        }
    },
};
</script>
