<template>
    <GuestLayout>
        <div class="w-full max-w-md">
            <h1 class="font-display font-semibold text-4xl text-center mb-4 mx-6">Request password reset</h1>
            
            <Card class="p-6">
                <form v-if="!is_sent" @submit.prevent="submit" class="w-full max-w-sm">
                    <div class="mb-3">
                        <FormInput type="email" :id="elementId('email')" label="Email address" :error="errors.email" v-model="form.email" dusk="email" required />
                    </div>

                    <div class="d-flex justify-content-end align-items-center">
                        <SpinnerButton type="submit" class="btn btn-primary w-full" :loading="is_waiting">Send request</SpinnerButton>
                    </div>
                </form>
            </Card>
            
            <p class="text-center mt-4">Remembered your password? <router-link :to="{ name: 'auth.login' }" class="link">Log in</router-link></p>
        </div>
    </GuestLayout>
</template>

<script>
import Card from '@/components/Card.vue';
import FormInput from '@/components/forms/FormInput.vue';
import GuestLayout from '@/components/layouts/GuestLayout.vue';
import SpinnerButton from '@/components/SpinnerButton.vue';
import UniqueId from '@/mixins/UniqueId';
import { useAlertsStore } from '@/stores';

export default {
    components: {
        Card,
        FormInput,
        GuestLayout,
        SpinnerButton,
    },
    data() {
        return {
            'form': {
                'email': '',
            },
            'errors': {},
            'is_waiting': false,
            'is_sent': false
        };
    },
    inject: [
        'api',
    ],
    methods: {
        submit() {
            this.errors = {};
            this.is_waiting = true;

            this.api.post('auth/password/request', this.form)
                .then(() => {
                    useAlertsStore().push('Password request sent.');
                })
                .catch(error => {
                    if (error.response.status === 422) {
                        this.errors = error.body.errors;
                    }
                })
                .finally(() => {
                    this.is_waiting = false;
                });
        }
    },
    mixins: [
        UniqueId
    ],
};
</script>
