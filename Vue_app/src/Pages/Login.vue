<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import InputFull from '../Components/InputFull.vue'
import ButtonColor from '../Components/ButtonColor.vue'
import Checkbox from '../Components/Checkbox.vue'
import CardMobile from '../Components/CardMobile.vue'

const router = useRouter()

const form = reactive({
    email: '',
    password: '',
    remember: false,
})
const processing = ref(false)
const emailError = ref('')
const passwordError = ref('')

const API_URL = import.meta.env.VITE_API_URL ?? 'http://localhost:8090'

const submit = async () => {
    emailError.value = ''
    passwordError.value = ''
    processing.value = true
    try {
        const response = await fetch(`${API_URL}/auth/login`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ email: form.email, password: form.password, remember: form.remember }),
        })
        if (response.ok) {
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
                <ButtonColor @click="" text="white" bg="gray" class="">
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
        <div class="mt-4 block">
            <Checkbox
                name="remember"
                v-model:checked="form.remember"
                :label="$t('Remember me')"
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
<!-- 
<script setup>

</script>
<template>
<div tabindex="0" class="p-10  overflow-auto flex justify-center items-center bg-white dark:bg-gray-900/80 dark:bg-gradient-to-bl from-gray-800/80 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-blue-500">
    <slot />
</div>  
</template> -->