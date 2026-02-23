import '../css/app.css';

const router = buildRouter();
import api from '@core/api';
import App from '@core/components/App.vue';
import buildRouter from '@core/router';
import components from '@core/components';
import stores from '@core/stores';
import { createApp } from 'vue';

const app = createApp(App)
    .use(api)
    .use(components)
    .use(router)
    .use(stores)
    .mount('#app');
