import { defineStore } from 'pinia';

export const useHttp = defineStore('useHttp', () => {
    const baseUrl = import.meta.env.VITE_APP_URL + '/api';

    const get = async (url, params = {}) => {
        const response = await fetch(`${baseUrl}${url}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
            params: params,
        });
        return response.json();
    };

    const post = async (url, data) => {
        const response = await fetch(`${baseUrl}${url}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data),
        });
        return response.json();
    };

    return { get, post };
});
