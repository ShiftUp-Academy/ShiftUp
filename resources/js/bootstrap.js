import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found');
}

window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response) {
            console.error('[AXIOS ERROR]', {
                status: error.response.status,
                url: error.config.url,
                data: error.response.data,
                message: error.message
            });
            if (error.response.status === 419) {
                window.location.reload();
            }
        } else {
            console.error('[AXIOS ERROR] Network or unknown error', error.message);
        }
        return Promise.reject(error);
    }
);

