import { configureEcho, echo } from '@laravel/echo-vue';

configureEcho({
    broadcaster: 'reverb',
});

export default {
    install(app) {
        app.provide('echo', echo());
    },
};
