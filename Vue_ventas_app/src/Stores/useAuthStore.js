import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useAuthStore = defineStore('auth', () => {
    const token = ref(localStorage.getItem('auth_token') ?? null)

    const isLoggedIn = computed(() => !!token.value)

    function setToken(newToken) {
        token.value = newToken
        localStorage.setItem('auth_token', newToken)
    }

    function clearToken() {
        token.value = null
        localStorage.removeItem('auth_token')
    }

    return { token, isLoggedIn, setToken, clearToken }
})
