import { mande } from 'mande';
import { useAlertsStore, useAuthStore } from '@/stores';

export class Api {
    constructor(base) {
        this.base = base || 'api';
    }

    client() {
        const options = {
            credentials: 'same-origin',
            headers: {
                'X-XSRF-TOKEN': this.getCsrfToken(),
            },
        };

        return mande('/', options);
    }

    get(url, options) {
        return this.client()
            .get(this.base + '/' + url, options)
            .catch(error => this.handleError(error));
    }

    post(url, data, options) {
        return this.client()
            .post(this.base + '/' + url, data, options)
            .catch(error => this.handleError(error, 'post'));
    }

    getCsrfToken() {
        const token = this.readCsrfToken();

        if (token) {
            return token;
        }

        mande('app').get(); // This should set a new one.

        return this.readCsrfToken();
    }

    readCsrfToken() {
        const name = 'XSRF-TOKEN=';
        const cookies = decodeURIComponent(document.cookie).split(';');
        const csrf_cookie = cookies.find(cookie => cookie.trim().startsWith(name));

        if (!csrf_cookie) {
            return null;
        }

        return csrf_cookie.substring(name.length);
    }

    handleError(error, method = 'get') {
        const http_status_code = error.response.status;

        if (http_status_code === 401) {
            useAlertsStore().push('You are not logged in.', 'warning');

            const auth_store = useAuthStore();

            auth_store.status = null;
            auth_store.resume_session = method === 'post';
        } else if (http_status_code === 419) {
            useAlertsStore().push('CSRF token expired.', 'danger');
        } else if (http_status_code === 422) {
            useAlertsStore().push('A validation error occured.', 'danger');
        } else if (http_status_code === 429) {
            useAlertsStore().push('Too many requests. Try again soon.', 'warning');
        } else if (http_status_code === 403) {
            useAlertsStore().push(error.body.message ?? 'You do not have permission to do that.', 'warning');
        } else if (http_status_code === 404) {
            useAlertsStore().push('We could not find that resource.', 'danger');
        } else if (http_status_code >= 400 && http_status_code < 500) {
            useAlertsStore().push('Something about that request was wrong.', 'danger');
        } else if (http_status_code >= 500) {
            useAlertsStore().push('Something went wrong at our end.', 'danger');
        }

        throw error;
    }
}

export default {
    install(app, base) {
        app.provide('api', new Api(base));
    },
};
