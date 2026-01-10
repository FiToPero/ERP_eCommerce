import { defineStore } from 'pinia'

const ADMIN_LOGIN_URL = import.meta.env.VITE_ADMIN_URL ?? 'http://localhost:8090/admin'

export const useLayoutStore = defineStore('layout', () => {
    function showLogin() {
        window.location.assign(ADMIN_LOGIN_URL)
    }

    return {
        showLogin,
    }
})