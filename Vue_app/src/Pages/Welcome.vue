<script setup>
import LayoutMain from '@/Layouts/LayoutMain.vue';
import ProductCardVertical from '@/Components/ProductCardVertical.vue';
import ProductCardHorizontal from '../Components/ProductCardHorizontal.vue';
import ProductCardDetails from '../Components/ProductCardDetails.vue';
import Banner from '../Components/Banner.vue';
import NavbarTop from '../Components/NavbarTop.vue';
import NavbarTopMenu from '../Components/NavbarTopMenu.vue';
</script>

<template>
    <!-- <LayoutMain> -->
        <!-- <NavbarTop /> -->
        <NavbarTopMenu />
        <!-- <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Welcome
            </h2>
        </template> -->
        <section class="min-h-screen bg-gray-100 dark:bg-gray-900 p-5">
            <!-- <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 place-items-center">
                <div tabindex="0"
                    class="col-span-full w-full max-w-4xl min-h-96 p-6 bg-white dark:bg-gray-800/50 from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex items-center justify-center text-center motion-safe:hover:scale-[1.01] transition-all duration-300 focus:outline-red-500"
                >
                    <p class="text-lg text-gray-800 dark:text-gray-100">
                        Welcome to your Vue.js application!
                        <br><br>
                        <h1 class="text-8xl font-bold text-green-500">Welcome</h1>
                    </p>
                </div>
            </div> -->
            <Banner />
            <!-- <div class="my-5 grid grid-cols-1 justify-center md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-1 place-items-center"> -->
            <div class="my-5 flex flex-wrap items-start justify-center gap-6"> 
                <ProductCardVertical />
                <ProductCardVertical />
                <ProductCardVertical />
                <ProductCardVertical />
                <ProductCardVertical />
                <ProductCardVertical />
            </div>
            <!-- <div class="my-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-1 place-items-center"> -->
            <div class="my-5 flex flex-wrap items-start justify-center gap-6">
                <ProductCardHorizontal />
                <ProductCardHorizontal />
                <ProductCardHorizontal />
                <ProductCardHorizontal />
                <ProductCardHorizontal />
            </div>
        </section>
        <!-- <ProductCardDetails /> -->
    <!-- </LayoutMain> -->
</template>
