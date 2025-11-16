import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Accept'] = 'application/json';
window.axios.defaults.withCredentials = true; // Important: Always send cookies with requests

// Set CSRF token for Laravel
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

// Request interceptor for debugging
window.axios.interceptors.request.use(
    (config) => {
        console.log('📨 Axios Request:', {
            url: config.url,
            method: config.method,
            headers: config.headers,
        });
        return config;
    },
    (error) => {
        console.error('📨 Request Error:', error);
        return Promise.reject(error);
    }
);

// Response interceptor
window.axios.interceptors.response.use(
    (response) => {
        console.log('📥 Axios Response:', {
            url: response.config.url,
            status: response.status,
            data: response.data,
        });
        return response;
    },
    (error) => {
        console.error('📥 Response Error:', {
            url: error.config?.url,
            status: error.response?.status,
            data: error.response?.data,
            message: error.message,
        });
        
        // If CSRF token mismatch (419), refresh the page
        if (error.response?.status === 419) {
            console.warn('🔄 CSRF token mismatch, reloading page...');
            window.location.reload();
        }
        return Promise.reject(error);
    }
);

