import '../css/app.css';

import api from '@core/api';
import App from '@core/components/App.vue';
import components from '@core/components';
import router from '@core/router';
import stores from '@core/stores';
import { createApp } from 'vue';

const app = createApp(App)
    .use(api)
    .use(components)
    .use(router)
    .use(stores)
    .mount('#app');
