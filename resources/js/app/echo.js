import { configureEcho, echo } from '@laravel/echo-vue';

configureEcho({
    broadcaster: import.meta.env.VITE_BROADCAST_CONNECTION || 'null',
});

export default {
    install(app) {
        app.provide('echo', echo());
    },
};
