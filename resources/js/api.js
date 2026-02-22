import { mande } from 'mande';
import { useAlertsStore } from '@core/stores';
import { isBefore, subMinutes } from 'date-fns';

// TODO Switch to getting CSRF on demand and replaying requests if necessary.

let csrf_last_refreshed_at = null;

async function getCsrfToken() {
    if (!csrf_last_refreshed_at || isBefore(csrf_last_refreshed_at, subMinutes(new Date(), 60))) {
        await mande('/sanctum/csrf-cookie').get();

        csrf_last_refreshed_at = new Date();
    }

    const name = 'XSRF-TOKEN=';
    const cookies = decodeURIComponent(document.cookie).split(';');

    const csrf_cookie = cookies.find(cookie => cookie.trim().startsWith(name));

    if (!csrf_cookie) {
        throw new Error('Could not find CSRF cookie.');
    }

    return csrf_cookie.substring(name.length);
}

function handleError(error, method) {
    const http_status_code = error.response.status;

    if (http_status_code === 422) {
        useAlertsStore().push('A validation error occured.', 'danger');
    } else if (http_status_code === 419) {
        refreshCsrfToken();
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

const api = {
    get: (url, options) => {
        return mande('/api/', { credentials: 'same-origin' })
            .get(url, options)
            .then(response => {
                if (!csrf_last_refreshed_at) {
                    csrf_last_refreshed_at = new Date();
                }

                return response;
            })
            .catch(error => handleError(error, 'get'));
    },
    post: async (url, data, options) => {
        return mande('/api/', {
            credentials: 'same-origin',
            headers: { 'X-XSRF-TOKEN': await getCsrfToken() }
        })
            .post(url, data, options)
            .catch(error => handleError(error, 'post'));
    },
};

api.install = app => {
    app.config.globalProperties.$api = api;
    app.provide('api', api);
    globalThis.$api = api;
};

export default api;
