import { defineStore } from 'pinia'
// Este import comparte la URL del login del admin para que la redirección no dependa de un puerto fijo.
import { defaultAdminLoginUrl } from '@/config/runtimeUrls'

// Esta constante envía al usuario al panel Laravel correcto aun si no se cargó un `.env` de Vue.
const ADMIN_LOGIN_URL = import.meta.env.VITE_ADMIN_URL ?? defaultAdminLoginUrl

export const useLayoutStore = defineStore('layout', () => {
    function showLogin() {
        window.location.assign(ADMIN_LOGIN_URL)
    }

    return {
        showLogin,
    }
})