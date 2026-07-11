<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import InputFull from '../Components/InputFull.vue'
import ButtonColor from '../Components/ButtonColor.vue'
import CardMobile from '../Components/CardMobile.vue'
import { useAuthStore } from '../Stores/useAuthStore'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
    user_name: '',
    email: '',
    password: '',
    password_confirmation: '',
})
const processing = ref(false)
const errors = reactive({
    user_name: '',
    email: '',
    password: '',
    general: '',
})

const API_URL = import.meta.env.VITE_API_URL ?? 'http://localhost:8090'

const submit = async () => {
    errors.user_name = ''
    errors.email = ''
    errors.password = ''
    errors.general = ''
    processing.value = true
    try {
        const response = await fetch(`${API_URL}/auth/register`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({
                user_name: form.user_name,
                email: form.email,
                password: form.password,
                password_confirmation: form.password_confirmation,
            }),
        })
        const data = await response.json()
        if (response.ok) {
            authStore.setToken(data.token)
            router.push('/')
        } else {
            errors.user_name = data.errors?.user_name?.[0] ?? ''
            errors.email     = data.errors?.email?.[0] ?? ''
            errors.password  = data.errors?.password?.[0] ?? ''
            errors.general   = (!data.errors && data.message) ? data.message : ''
        }
    } catch (e) {
        errors.general = 'Connection error'
    } finally {
        processing.value = false
    }
}
</script>

<template>
<div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-60 z-50">
    <CardMobile>
        <form @submit.prevent="submit" class="w-96">
            <div class="flex justify-center items-center mb-5">
                <span class="w-11/12 flex justify-center text-white dark:text-gray-300 text-3xl font-bold">
                    {{ $t('Register') }}
                </span>
                <div class="w-1/12 flex justify-end">
                    <ButtonColor @click="router.push('/')" text="white" bg="gray">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                    </ButtonColor>
                </div>
            </div>

            <div v-if="errors.general" class="mx-2 mb-3 text-red-500 text-sm font-bold text-center">
                {{ errors.general }}
            </div>

            <div class="mx-2">
                <InputFull
                    v-model:model="form.user_name"
                    v-model:errors="errors.user_name"
                    :label="$t('Name')"
                    type="text"
                    id="user_name"
                    :autofocus="true"
                />
            </div>
            <div class="mx-2">
                <InputFull
                    v-model:model="form.email"
                    v-model:errors="errors.email"
                    :label="$t('Email')"
                    type="text"
                    id="email"
                />
            </div>
            <div class="mx-2">
                <InputFull
                    v-model:model="form.password"
                    v-model:errors="errors.password"
                    :label="$t('Password')"
                    type="password"
                    id="password"
                />
            </div>
            <div class="mx-2">
                <InputFull
                    v-model:model="form.password_confirmation"
                    v-model:errors="errors.password"
                    :label="$t('Confirm Password')"
                    type="password"
                    id="password_confirmation"
                />
            </div>

            <div class="mt-4 flex items-center justify-between">
                <ButtonColor
                    text="white" bg="green"
                    :class="{ 'opacity-25': processing }"
                    :disabled="processing"
                >
                    {{ $t('Register') }}
                </ButtonColor>
                <a
                    class="text-sm text-gray-400 hover:text-white cursor-pointer"
                    @click="router.push('/login')"
                >
                    {{ $t('Already have an account?') }}
                </a>
            </div>
        </form>
    </CardMobile>
</div>
</template>
