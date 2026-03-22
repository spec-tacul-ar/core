<template>
    <RouterView v-slot="{ Component }" :key="route_key">
        <ErrorMessage v-if="error === 'not_found'" icon="question" title="Not found">
            The resource you are looking for cannot be found.
        </ErrorMessage>

        <ErrorMessage v-else-if="error" />
        
        <Suspense v-else-if='Component' timeout="0">
            <component :is="Component" />

            <template #fallback>
                <div class="h-full flex items-center justify-center">
                    <LoadingSpinner label="Loading..." />
                </div>
            </template>
        </Suspense>
    </RouterView>

    <ModalManager />

    <div class="fixed bottom-0 w-full max-w-sm m-4 z-30">
        <TransitionGroup
            name="custom-classes"
            enter-from-class="opacity-0 scale-95"
            enter-active-class="motion-reduce:transition-none transition"
            leave-active-class="motion-reduce:transition-none transition"
            leave-to-class="opacity-0 scale-95">
            
            <Alert v-for="(alert, index) in alerts" :key="index" :alert="alert" />
        </TransitionGroup>
    </div>
</template>

<script>
import Alert from '@/components/Alert.vue';
import ErrorMessage from '@/components/ErrorMessage.vue';
import LoadingSpinner from '@/components/LoadingSpinner.vue';
import ModalManager from '@/components/ModalManager.vue';
import ResumeSession from '@/components/modals/ResumeSession.vue';
import { useAlertsStore, useAuthStore, useModalStore } from '@/stores';

export default {
    components: {
        Alert,
        ErrorMessage,
        LoadingSpinner,
        ModalManager,
    },
    computed: {
        alerts() {
            return useAlertsStore().alerts;
        },
        is_logged_in() {
            return useAuthStore().is_logged_in;
        },
        route_key() {
            if (this.$route.matched.length < 1) {
                return this.$route.path;
            }

            const part_count = this.$route.matched[0].path.split('/').length;

            return this.$route.path.split('/').slice(0, part_count).join('/');
        }
    },
    data () {
        return {
            error: null,
            is_loading: null,
        };
    },
    errorCaptured(error) {
        const status_code = error.response?.status;

        if (status_code === 404) {
            this.error = 'not_found';
        } else {
            this.error = true;

            console.error(error);
        }

        return false;
    },
    mounted() {
        window.addEventListener('keydown', this.handleKeyboardEvent);
    },
    unmounted() {
        window.removeEventListener('keydown', this.handleKeyboardEvent);
    },
    watch: {
        '$route': {
            handler(route) {
                if (route.meta.title) {
                    document.title = route.meta.title;
                }

                this.error = null;
            },
        },
        is_logged_in(is_logged_in) {
            if (!is_logged_in && this.$route.meta?.auth !== 'guest') {
                if (useAuthStore().resume_session) {
                    useModalStore().open(ResumeSession);
                    return;
                }

                this.$router.push({ name: 'auth.login' });
            }
        },
    },
};
</script>
