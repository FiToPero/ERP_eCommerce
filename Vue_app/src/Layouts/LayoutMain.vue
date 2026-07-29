<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import ApplicationLogo from '../Components/ApplicationLogo.vue'
import NavLink from '../Components/NavLink.vue'
import ButtonColor from '../Components/ButtonColor.vue'
import { useAuthStore } from '../Stores/useAuthStore'
import { storeToRefs } from 'pinia'

///// pinia ////
const authStore = useAuthStore()
const { isLoggedIn } = storeToRefs(authStore)

const API_URL = import.meta.env.VITE_API_URL ?? 'http://localhost:8090'

const router = useRouter()

const logout = async () => {
    try {
        await fetch(`${API_URL}/auth/logout`, {
            method: 'POST',
            headers: { 'Authorization': `Bearer ${authStore.token}`, 'Accept': 'application/json' },
        })
    } finally {
        authStore.clearToken()
        router.push('/login')
    }
}
</script>

<template>
    <nav class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
        <!-- Primary Navigation Menu -->
        <div class=" px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <!-- Logo -->
                    <div class="shrink-0 flex items-center">
                        <a href="#">
                            <ApplicationLogo
                                class="block h-24 w-auto fill-current text-gray-800 dark:text-gray-200"
                            />
                        </a>
                    </div>
                    <!-- Navigation Links -->
                    <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                        <NavLink href="#" active="">
                            Welcome
                        </NavLink>
                        
                    </div>
                </div>
                <div class="">
                    <template v-if="!isLoggedIn">
                        <ButtonColor text="white" bg="gray" @click="router.push('/register')" class="m-3">Register</ButtonColor>
                        <ButtonColor text="white" bg="gray" @click="router.push('/login')" class="m-3">Login</ButtonColor>
                    </template>
                    <template v-else>
                        <ButtonColor text="white" bg="red" @click="logout" class="m-3">Logout</ButtonColor>
                    </template>
                </div>
            </div>
        </div>    
    </nav>
    

    <!-- Page Heading -->
    <header class="bg-white dark:bg-gray-800 shadow" v-if="$slots.header">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <slot name="header" />
        </div>
    </header>

    <!-- Page Content -->
    <main>

        <slot />
    </main>
</template>
