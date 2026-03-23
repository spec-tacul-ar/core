<template>
    <GuestLayout>
        <div class="w-full max-w-sm">
            <h1 class="font-display font-semibold text-4xl text-center mb-4 mx-6">Register</h1>

            <Card class="p-6">
                <form @submit.prevent="submit">
                    <div class="mb-3">
                        <FormInput type="text" :id="elementId('name')" label="Name" :error="errors.name" v-model="form.name" dusk="name" required />
                    </div>
                    <div class="mb-3">
                        <FormInput type="email" :id="elementId('email')" label="Email address" :error="errors.email" v-model="form.email" dusk="email" required />
                    </div>
                    <div class="mb-3">
                        <div>
                            <FormInput
                                :type="show_password ? 'text' : 'password'"
                                :id="elementId('password')"
                                label="Password"
                                :error="errors.password"
                                v-model="form.password"
                                dusk="password"
                                class="pr-10"
                                required>

                                <template v-slot:append>
                                    <button type="button" title="Show password" class="absolute inset-y-0 right-0 p-2 text-gray-800 hover:text-black transition-colors duration-500" @click="show_password = !show_password">
                                        <IconSet name="show" v-if="show_password" class="size-6" />
                                        <IconSet name="hide" v-if="!show_password" class="size-6" />
                                    </button>
                                </template>
                            </FormInput>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end align-items-center">
                        <SpinnerButton type="submit" class="btn btn-primary w-full" :loading="is_waiting">Register</SpinnerButton>
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
                        <a v-if="socials.includes('linkedin')" href="/auth/linkedin/redirect" class="btn btn-primary-outline w-full normal-case">
                            <IconSet name="linkedin" /> Continue with LinkedIn
                        </a>
                    </div>
                </div>
            </Card>
    
            <p class="text-center mt-4">Already registered? <router-link :to="{ name: 'auth.login' }" class="link">Log in</router-link></p>
        </div>
    </GuestLayout>
</template>

<script>
import Card from '@/components/Card.vue';
import FormInput from '@/components/forms/FormInput.vue';
import GuestLayout from '@/components/layouts/GuestLayout.vue';
import Model from '@/stores/Model.js';
import SpinnerButton from '@/components/SpinnerButton.vue';
import UniqueId from '@/mixins/UniqueId';
import { useAlertsStore } from '@/stores';
import { useAuthStore } from '@/stores';
import IconSet from '@/components/IconSet.vue';

export default {
    components: {
        Card,
        FormInput,
        GuestLayout,
        IconSet,
        SpinnerButton
    },
    data() {
        return {
            'errors': {},
            'form': {
                'name': '',
                'email': '',
                'password': ''
            },
            'is_waiting': false,
            'show_password': false
        };
    },
    computed: {
        socials() {
            return Spectacular.auth_providers;
        },
    },
    inject: [
        'api',
    ],
    methods: {
        submit: function () {
            this.show_password = false;

            this.errors = {};
            this.is_waiting = true;

            this.api.post('auth/register', this.form)
                .then(() => {
                    Model.resetRepositories();
                    
                    useAuthStore().is_logged_in = true;
                    useAlertsStore().push('You are now registered.');

                    this.$router.push({ name: 'projects.index' });
                })
                .catch(error => this.errors = error.body.errors ?? {})
                .finally(() => this.is_waiting = false);
        }
    },
    mixins: [
        UniqueId
    ],
};
</script>
