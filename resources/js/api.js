import { mande } from 'mande';
import { useAlertsStore } from '@core/stores';
import { isBefore, subMinutes } from 'date-fns';

// TODO Switch to getting CSRF on demand and replaying requests if necessary.
export class Api {
    constructor(base = '/api/') {
        this.base = base;
        this.csrf_last_refreshed_at = null;
    }

    get(url, options) {
        return mande(this.base, { credentials: 'same-origin' })
            .get(url, options)
            .then(response => {
                if (!this.csrf_last_refreshed_at) {
                    this.csrf_last_refreshed_at = new Date();
                }

                return response;
            })
            .catch(error => this.handleError(error, 'get'));
    }

    async post(url, data, options) {
        return mande(this.base, {
            credentials: 'same-origin',
            headers: { 'X-XSRF-TOKEN': await this.getCsrfToken() }
        })
            .post(url, data, options)
            .catch(error => this.handleError(error, 'post'));
    }

    async getCsrfToken() {
        if (!this.csrf_last_refreshed_at || isBefore(this.csrf_last_refreshed_at, subMinutes(new Date(), 60))) {
            await mande('/sanctum/csrf-cookie').get();

            this.csrf_last_refreshed_at = new Date();
        }

        const name = 'XSRF-TOKEN=';
        const cookies = decodeURIComponent(document.cookie).split(';');

        const csrf_cookie = cookies.find(cookie => cookie.trim().startsWith(name));

        if (!csrf_cookie) {
            throw new Error('Could not find CSRF cookie.');
        }

        return csrf_cookie.substring(name.length);
    }

    handleError(error, method) {
        const http_status_code = error.response.status;

        if (http_status_code === 422) {
            useAlertsStore().push('A validation error occured.', 'danger');
        } else if (http_status_code === 419) {
            this.getCsrfToken();
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
  install(app, base = '/api/') {
    app.provide('api', new Api(base));
  },
};

