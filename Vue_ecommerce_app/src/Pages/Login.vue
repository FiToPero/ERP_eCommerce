<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import InputFull from '../Components/InputFull.vue'
import ButtonColor from '../Components/ButtonColor.vue'
import Checkbox from '../Components/Checkbox.vue'
import CardMobile from '../Components/CardMobile.vue'
import { useAuthStore } from '../Stores/useAuthStore'
// Este import usa una URL base compartida para que el login no dependa de `localhost:8090`.
import { defaultApiOrigin } from '@/config/runtimeUrls'
// Mensage de Verificación
import { useRoute } from 'vue-router'
const route = useRoute()
const verifiedMessage = route.query.verified === '1' ? 'Email verified. You can log in now.' : ''

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
    email: '',
    password: '',
})
const processing = ref(false)
const emailError = ref('')
const passwordError = ref('')
const forgotProcessing = ref(false)
const forgotMessage = ref('')
const forgotError = ref('')

// Esta constante resuelve el backend del admin por dominio cuando no se inyectó `VITE_API_URL`.
const API_URL = import.meta.env.VITE_API_URL ?? defaultApiOrigin

const sendResetLink = async () => {
    forgotMessage.value = ''
    forgotError.value = ''

    if (!form.email.trim()) {
        forgotError.value = 'Enter your email address first.'
        return
    }

    forgotProcessing.value = true
    try {
        const response = await fetch(`${API_URL}/auth/forgot-password`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ email: form.email }),
        })
        const data = await response.json()

        if (response.ok) {
            forgotMessage.value = data.message ?? 'If the email is registered, a password reset link will be sent.'
        } else {
            forgotError.value = data.errors?.email?.[0] ?? data.message ?? 'Unable to send the password reset link.'
        }
    } catch (e) {
        forgotError.value = 'Connection error'
    } finally {
        forgotProcessing.value = false
    }
}

const submit = async () => {
    emailError.value = ''
    passwordError.value = ''
    processing.value = true
    try {
        const response = await fetch(`${API_URL}/auth/login`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ email: form.email, password: form.password }),
        })
        if (response.ok) {
            const data = await response.json()
            authStore.setToken(data.token)
            router.push('/')
        } else {
            const data = await response.json()
            emailError.value = data.errors?.email?.[0] ?? ''
            passwordError.value = data.errors?.password?.[0] ?? (data.message ?? '')
        }
    } catch (e) {
        passwordError.value = 'Connection error'
    } finally {
        processing.value = false
    }
}
</script>
<template>
    <h1 class="text-8xl font-bold text-green-500">Login</h1>
<div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-60 z-50">
    <CardMobile>
    <form @submit.prevent="submit" class="w-96">
        <div class="flex justify-center items-center mb-5">
            <span class="w-11/12 flex justify-center text-white dark:text-gray-300 text-3xl font-bold">{{ $t('Login') }}</span>
            <div class="w-1/12 flex justify-end">
                <ButtonColor @click="router.push('/')" text="white" bg="gray" class="">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                </ButtonColor>
            </div>
        </div>
        <div class="mx-2">
            <InputFull
                v-model:model="form.email"
                v-model:errors="emailError"
                :label="$t('Email')"
                :type="'text'"
                :id="'email'"
                ref="email"
                :autofocus="true"
            />
        </div>
        <div class="mx-2 mt-1 text-right">
            <a
                href="#"
                class="text-sm text-gray-400 hover:text-white"
                :class="{ 'pointer-events-none opacity-50': forgotProcessing }"
                :aria-busy="forgotProcessing"
                @click.prevent="sendResetLink"
            >
                {{ forgotProcessing ? 'Sending...' : 'Forgot password?' }}
            </a>
        </div>
        <p v-if="forgotMessage" class="mx-2 mt-2 text-sm text-green-400" role="status">
            {{ forgotMessage }}
        </p>
        <p v-if="forgotError" class="mx-2 mt-2 text-sm text-red-400" role="alert">
            {{ forgotError }}
        </p>
        <div class="mx-2">
        <InputFull
                v-model:model="form.password"
                v-model:errors="passwordError"
                :label="$t('Password')"
                :type="'password'"
                :id="'password'"
                ref="password"
            />
        </div>
        <div class="mt-4 flex items-center justify-between">
            <ButtonColor
                text="white" bg="green"
                :class="{ 'opacity-25': processing }"
                :disabled="processing"
            >
                {{ $t('Login') }}
            </ButtonColor>
            <a
                class="text-sm text-gray-400 hover:text-white cursor-pointer"
                @click="router.push('/register')"
            >
                {{ $t('Create an account') }}
            </a>
        </div>
    </form>
    </CardMobile>
</div>
</template>
