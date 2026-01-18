import { ref } from 'vue';

export function useApi() {
    const loading = ref(false);
    const error = ref(null);

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken
    };

    async function request(url, options = {}) {
        loading.value = true;
        error.value = null;

        try {
            const response = await fetch(url, {
                ...options,
                headers: { ...headers, ...options.headers }
            });

            if (!response.ok) {
                const data = await response.json().catch(() => ({}));
                throw new Error(data.message || `Error ${response.status}`);
            }

            const data = await response.json();
            return data;
        } catch (err) {
            error.value = err.message;
            throw err;
        } finally {
            loading.value = false;
        }
    }

    async function get(url) {
        return request(url, { method: 'GET' });
    }

    async function post(url, data) {
        return request(url, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    async function put(url, data) {
        return request(url, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    async function del(url) {
        return request(url, { method: 'DELETE' });
    }

    return {
        loading,
        error,
        get,
        post,
        put,
        del
    };
}
