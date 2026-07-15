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

                <div v-if="socials.length" class="mt-4">
                    <div class="flex items-center gap-3 text-xs uppercase text-gray-500">
                        <span class="h-px flex-1 bg-gray-200"></span>
                        <span>Or</span>
                        <span class="h-px flex-1 bg-gray-200"></span>
                    </div>

                    <div class="mt-3 grid gap-2">
                        <a v-if="socials.includes('google')" href="/auth/google/redirect" class="btn btn-primary-outline w-full normal-case">
                            <IconSet name="google" /> Continue with Google
                        </a>
                        <a v-if="socials.includes('github')" href="/auth/github/redirect" class="btn btn-primary-outline w-full normal-case">
                            <IconSet name="github" /> Continue with GitHub
                        </a>
                        <a v-if="socials.includes('linkedin-openid')" href="/auth/linkedin-openid/redirect" class="btn btn-primary-outline w-full normal-case">
                            <IconSet name="linkedin" /> Continue with LinkedIn
                        </a>
                    </div>
                </div>
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
        'settings',
    ],
    computed: {
        socials() {
            return this.settings.socialite ?? [];
        },
    },
    methods: {
        submit() {
            this.errors = {};
            this.is_waiting = true;

            this.api.client().post('auth/login', this.form)
                .then(() => {
                    useAuthStore().resume_session = true;

                    this.errors = {};
                    this.form.email = '';
                    this.form.password = '';

                    this.$router.push({ name: 'projects.index' });

                    useAlertsStore().push('You have been logged in.');
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
