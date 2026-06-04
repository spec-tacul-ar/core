<template>
    <ModalLayout locked>
        <h2 class="text-4xl font-light mb-4">Session expired</h2>

        <form @submit.prevent="submit">
            <p class="mb-4">Please enter your password again to continue.</p>
                
            <div v-if="!account" class="mb-4">
                <form-input type="email" :id="elementId('email')" label="Email address" :error="errors.email" v-model="form.email" required />
            </div>
            <div class="mb-4">
                <form-input type="password" :id="elementId('password')" label="Password" :error="errors.password" v-model="form.password" required />
            </div>

            <SpinnerButton type="submit" class="btn btn-primary" :loading="is_waiting">Resume session</SpinnerButton>
        </form>
    </ModalLayout>
</template>

<script>
import FormInput from '@/components/forms/FormInput.vue';
import ModalLayout from '@/components/layouts/ModalLayout.vue';
import SpinnerButton from '@/components/SpinnerButton.vue';
import UniqueId from '@/mixins/UniqueId';
import { useAlertsStore } from '@/stores';
import { useAuthStore } from '@/stores';

export default {
    components: {
        FormInput,
        ModalLayout,
        SpinnerButton,
    },
    computed: {
        account() {
            return useAuthStore().account;
        },
    },
    data() {
        const account = useAuthStore().account;

        return {
            'form': {
                'email': account?.email,
                'password': '',
                'remember': false
            },
            'errors': {},
            'is_waiting': false
        };
    },
    inject: [
        'api',
    ],
    methods: {
        submit() {
            this.errors = {};
            this.is_waiting = true;

            this.api.client().post('auth/login', {
                email: this.form.email,
                password: this.form.password,
            })
                .then(async () => {
                    useAuthStore().status = await this.api.client().get('auth/check');

                    useAlertsStore().push('You have been logged in.');

                    this.$emit('close');
                })
                .catch((error) => {
                    if (error.response.status === 422) {
                        this.errors = error.body.errors;
                    }
                })
                .finally(() => this.is_waiting = false);
        },
    },
    mixins: [
        UniqueId,
    ],
};
</script>
