<script setup>
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import ButtonColor from '../Components/ButtonColor.vue'
import CardMobile from '../Components/CardMobile.vue'

const route = useRoute()
const router = useRouter()
const token = typeof route.query.token === 'string' ? route.query.token : ''
const email = typeof route.query.email === 'string' ? route.query.email : ''
const password = ref('')
const confirmation = ref('')
const processing = ref(false)
const error = ref('')
const completed = ref(false)

onMounted(() => {
    const savedTheme = localStorage.getItem('theme')
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
    document.documentElement.classList.toggle('dark', savedTheme ? savedTheme === 'dark' : prefersDark)
})

const submit = async () => {
    error.value = ''
    processing.value = true
    try {
        const response = await fetch(`${import.meta.env.VITE_API_URL}/auth/reset-password`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ token, email, password: password.value, password_confirmation: confirmation.value }),
        })
        const data = await response.json()
        if (!response.ok) {
            error.value = data.errors?.password?.[0] ?? data.message ?? 'Unable to reset the password.'
            return
        }
        password.value = ''
        confirmation.value = ''
        completed.value = true
        await router.replace({ path: '/reset-password' })
    } catch (e) {
        error.value = 'Connection error'
    } finally {
        processing.value = false
    }
}
</script>

<template>
<div class="fixed inset-0 flex items-center justify-center bg-black/60 p-4 z-50">
    <CardMobile>
        <div class="w-80 max-w-full">
            <h1 class="text-gray-900 dark:text-gray-100 text-3xl font-bold mb-4">Reset password</h1>
            <p v-if="completed" class="text-green-700 dark:text-green-400" role="status">Password reset successfully.</p>
            <p v-else-if="!token || !email" class="text-red-700 dark:text-red-400" role="alert">Invalid password reset link.</p>
            <form v-else @submit.prevent="submit">
                <p class="text-gray-700 dark:text-gray-300 mb-4 break-all">{{ email }}</p>
                <label for="password" class="block text-gray-900 dark:text-white mb-1">New password</label>
                <input id="password" v-model="password" type="password" autocomplete="new-password" required
                    class="w-full p-2 mb-4 rounded border border-gray-300 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                <label for="password_confirmation" class="block text-gray-900 dark:text-white mb-1">Confirm password</label>
                <input id="password_confirmation" v-model="confirmation" type="password" autocomplete="new-password" required
                    class="w-full p-2 mb-4 rounded border border-gray-300 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                <p v-if="error" class="text-red-700 dark:text-red-400 mb-4" role="alert">{{ error }}</p>
                <ButtonColor type="submit" text="white" bg="green" :disabled="processing">
                    {{ processing ? 'Saving...' : 'Reset password' }}
                </ButtonColor>
            </form>
            <button type="button" class="text-gray-700 hover:text-gray-950 dark:text-gray-300 dark:hover:text-white mt-4" @click="router.push('/login')">Go to login</button>
        </div>
    </CardMobile>
</div>
</template>
