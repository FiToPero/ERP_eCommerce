<script setup>
import { ref, computed } from 'vue'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import NavLink from '@/Components/NavLink.vue'

import ButtonColor from '@/Components/ButtonColor.vue'
import { useLayoutStore } from '@/Stores/useLayoutStore'
import { storeToRefs } from 'pinia'


///// pinia ////
const storeLayout = useLayoutStore()
const {  } = storeToRefs(storeLayout)
const { showLogin } = storeLayout


</script>

<template>
    <nav class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
        <!-- Primary Navigation Menu -->
        <div class=" px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <!-- Logo -->
                    <div class="shrink-0 flex items-center">
                        <Link href="#">
                            <ApplicationLogo
                                class="block h-24 w-auto fill-current text-gray-800 dark:text-gray-200"
                            />
                        </Link>
                    </div>
                    <!-- Navigation Links -->
                    <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                        <NavLink href="#" active="">
                            Welcome
                        </NavLink>
                        
                    </div>
                </div>
                <div class="">
                    <ButtonColor text="white" bg="gray" @click="" class="m-3 " >Register</ButtonColor>
                    <ButtonColor text="white" bg="gray" @click="showLogin" class="m-3 ">Login</ButtonColor>                            
                </div>
                   
                  
     

                </div>
            </div>
        

        <!-- Responsive Navigation Menu -->
  

        
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
