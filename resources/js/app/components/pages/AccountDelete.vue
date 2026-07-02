<template>
    <DefaultLayout>
        <div class="w-full max-w-xl mx-auto">
            <h1 class="font-display font-semibold text-4xl text-center mb-4 mx-6">Delete account</h1>

            <Card class="border-red-400 border-2 p-6 dark:border-red-900">
                <form @submit.prevent="submit">
                    <p class="mb-4">Are you sure you want to delete your account?</p>
                    <p class="font-semibold text-red-400 mb-4 dark:text-red-300">This will permanently delete all projects where you are the sole owner and the data associate with them.</p>
                    
                    <div class="mb-4">
                        <FormInput
                            type="checkbox"
                            name="confirmation"
                            label="I understand this is irreversible"
                            id="confirmation"
                            v-model="form.confirmation"
                            required />
                    </div>

                    <SpinnerButton type="submit" class="btn btn-danger w-full" :loading="is_waiting" :disabled="!form.confirmation">Delete account</SpinnerButton>
                </form>
            </Card>
        </div>
    </DefaultLayout>
</template>

<script>
import Card from '@/components/Card.vue';
import DefaultLayout from '@/components/layouts/DefaultLayout.vue';
import FormInput from '@/components/forms/FormInput.vue';
import Model from '@/stores/Model.js';
import SpinnerButton from '@/components/SpinnerButton.vue';
import { useAlertsStore } from '@/stores';
import { useAuthStore } from '@/stores';

export default {
    components: {
        Card,
        DefaultLayout,
        FormInput,
        SpinnerButton,
    },
    data() {
        return {
            form: {
                confirmation: false,
            },
            errors: {},
            is_waiting: false,
        };
    },
    inject: [
        'api',
    ],
    methods: {
        submit() {
            this.errors = {};
            this.is_waiting = true;

            this.api.post('account/delete', this.form)
                .then(() => {
                    const auth_store = useAuthStore();

                    auth_store.resume_session = false;
                    auth_store.clearAccount();

                    Model.resetRepositories();
                    useAlertsStore().push('Your account was deleted.');
                })
                .catch(error => this.errors = error.body.errors ?? {})
                .finally(() => this.is_waiting = false);
        }
    },
};
</script>
