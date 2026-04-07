<template>
    <GuestLayout>
        <div class="w-full max-w-sm">
            <h1 class="font-display font-semibold text-4xl text-center mb-4 mx-6">Log in</h1>

            <Card class="p-8">
                <form @submit.prevent="submit">
                    <div class="mb-3">
                        <FormInput type="email" :id="elementId('email')" label="Email address" :error="errors.email" v-model="form.email" dusk="email" required />
                    </div>
                    <div class="mb-3">
                        <FormInput type="password" :id="elementId('password')" label="Password" :error="errors.password" v-model="form.password" dusk="password" required />
                    </div>
                    <div class="mb-3 flex">
                        <FormInput type="checkbox" :id="elementId('remember')" label="Remember me" :error="errors.remember" v-model="form.remember" />
                        <RouterLink :to="{ name: 'auth.password.request' }" class="link ml-auto">Reset password</RouterLink>
                    </div>

                    <div class="d-flex justify-content-end align-items-center">
                        <spinner-button type="submit" class="btn btn-primary w-full" :loading="is_waiting">Log in</spinner-button>
                    </div>
                </form>
            </Card>
            
            <p class="text-center mt-4">Don't have an account yet? <router-link :to="{ name: 'auth.register' }" class="link">Register</router-link></p>
        </div>
    </GuestLayout>
</template>

<script>
import Card from '@/components/Card.vue';
import FormInput from '@/components/forms/FormInput.vue';
import GuestLayout from '@/components/layouts/GuestLayout.vue';
import IconSet from '@/components/IconSet.vue';
import Model from '@/stores/Model.js';
import SpinnerButton from '@/components/SpinnerButton.vue';
import UniqueId from '@/mixins/UniqueId';
import { useAlertsStore } from '@/stores';
import { useAuthStore } from '@/stores';

export default {
    components: {
        Card,
        FormInput,
        GuestLayout,
        IconSet,
        SpinnerButton,
    },
    data() {
        return {
            'form': {
                'email': '',
                'password': '',
                'remember': false
            },
            'errors': {},
            'is_waiting': false,
        };
    },
    inject: [
        'api',
    ],
    methods: {
        submit() {
            this.errors = {};
            this.is_waiting = true;

            this.api.post('auth/login', this.form)
                .then(() => {
                    Model.resetRepositories();
                    
                    useAuthStore().is_logged_in = true;
                    useAlertsStore().push('You have been logged in.');

                    this.errors = {};
                    this.form.email = '';
                    this.form.password = '';

                    this.$router.push({ name: 'projects.index' });
                })
                .catch((error) => {
                    if (error.response.status === 422) {
                        this.errors = error.body.errors;
                    }

                    this.is_waiting = false;
                });
        }
    },
    mixins: [
        UniqueId
    ],
};
</script>
