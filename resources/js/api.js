import { mande } from 'mande';
import { useAlertsStore, useAuthStore } from '@/stores';

export class Api {
    constructor(base) {
        this.base = base || 'api';
    }

    get(url, options) {
        return mande('/' + this.base, { credentials: 'same-origin' })
            .get(url, options)
            .catch(error => this.handleError(error, 'get'));
    }

    async post(url, data, options) {
        return mande('/' + this.base, {
            credentials: 'same-origin',
            headers: { 'X-XSRF-TOKEN': await this.getCsrfToken() }
        })
            .post(url, data, options)
            .catch(error => this.handleError(error, 'post'));
    }

    async getCsrfToken() {
        let csrf_token = this.findCsrfToken();

        if (!csrf_token) {
            await mande('/sanctum/csrf-cookie').get();
            csrf_token = this.findCsrfToken();
        }

        if (!csrf_token) {
            throw new Error('Could not find CSRF cookie.');
        }

        return csrf_token;
    }

    findCsrfToken() {
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

            auth_store.is_logged_in = false;
            auth_store.resume_session = method === 'post';
        } else if (http_status_code === 419) {
            this.getCsrfToken();
        } else if (http_status_code === 422) {
            useAlertsStore().push('A validation error occured.', 'danger');
        } else if (http_status_code === 429) {
            useAlertsStore().push('Too many requests. Try again soon.', 'warning');
        } else if (http_status_code === 403) {
            useAlertsStore().push(error.body.message ?? 'You do not have permission to do that.', 'warning');
        } else if (http_status_code >= 400 && http_status_code < 500) {
            useAlertsStore().push('Something about that request was wrong.', 'danger');
        } else if (http_status_code >= 500) {
            useAlertsStore().push('Something went wrong at our end.', 'danger');
        }

        throw error;
    }

    install(app, base = this.base) {
        app.provide('api', new Api(base));
    }
}

export default {
    install(app, base) {
        app.provide('api', new Api(base));
    },
};
