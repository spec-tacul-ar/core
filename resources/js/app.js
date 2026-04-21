import '../css/app.css';

import api from '@/api';
import App from '@/components/App.vue';
import router from '@/router';
import settings from '@/settings';
import stores from '@/stores';
import { createApp } from 'vue';

createApp(App)
    .use(api)
    .use(router)
    .use(settings)
    .use(stores)
    .mount('#app');
