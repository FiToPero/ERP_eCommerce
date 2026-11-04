<script setup>
import { onBeforeUnmount, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import ButtonColor from '../Components/ButtonColor.vue'
import CardMobile from '../Components/CardMobile.vue'
import { defaultApiOrigin } from '@/config/runtimeUrls'

const route = useRoute()
const router = useRouter()

const API_URL = import.meta.env.VITE_API_URL ?? defaultApiOrigin
const email = route.query.email ?? ''

const processing = ref(false)
const cooldown = ref(0)
const message = ref('')
const error = ref('')
let timer = null

const startCooldown = () => {
    cooldown.value = 60
    timer = setInterval(() => {
        cooldown.value--
        if (cooldown.value <= 0) {
            clearInterval(timer)
        }
    }, 1000)
}

const resend = async () => {
    message.value = ''
    error.value = ''
    processing.value = true
    try {
        const response = await fetch(`${API_URL}/auth/email/resend`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ email }),
        })
        const data = await response.json()
        if (response.ok) {
            message.value = data.message ?? 'Verification email sent.'
            startCooldown()
        } else {
            error.value = data.errors?.email?.[0] ?? data.message ?? 'Unable to resend the email.'
        }
    } catch (e) {
        error.value = 'Connection error'
    } finally {
        processing.value = false
    }
}

onBeforeUnmount(() => clearInterval(timer))
</script>

<template>
<div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-60 z-50">
    <CardMobile>
        <div class="w-96 text-center">
            <span class="block text-white dark:text-gray-300 text-3xl font-bold mb-4">
                {{ $t('Verify your email') }}
            </span>
            <p class="text-gray-300 text-sm">
                {{ $t('We sent a verification link to') }}
                <span v-if="email" class="font-bold text-white">{{ email }}</span>.
                {{ $t('Open it to activate your account, then log in.') }}
            </p>

            <p v-if="message" class="mt-4 text-sm text-green-400" role="status">{{ message }}</p>
            <p v-if="error" class="mt-4 text-sm text-red-400" role="alert">{{ error }}</p>

            <div class="mt-6 flex items-center justify-between">
                <ButtonColor
                    text="white" bg="green"
                    :class="{ 'opacity-25': processing || cooldown > 0 }"
                    :disabled="processing || cooldown > 0 || !email"
                    @click="resend"
                >
                    {{ cooldown > 0 ? `${$t('Resend email')} (${cooldown})` : $t('Resend email') }}
                </ButtonColor>
                <a class="text-sm text-gray-400 hover:text-white cursor-pointer" @click="router.push('/login')">
                    {{ $t('Go to login') }}
                </a>
            </div>
        </div>
    </CardMobile>
</div>
</template>
