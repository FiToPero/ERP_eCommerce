<script setup>
import { ref, onMounted } from 'vue';
import ProductCardHorizontal from '../Components/ProductCardHorizontal.vue';
import ProductCardDetails from '../Components/ProductCardDetails.vue';
import NavbarTopMenu from '../Components/NavbarTopMenu.vue';
// Este import centraliza la URL de la API para el catálogo público de la SPA.
import { defaultApiOrigin } from '@/config/runtimeUrls';

// Esta constante usa el origen de la API por dominio y evita caer al puerto 8090 antiguo.
const API_URL = import.meta.env.VITE_API_URL ?? defaultApiOrigin;
const products = ref([]);

onMounted(async () => {
    const response = await fetch(`${API_URL}/products/index`);
    products.value = await response.json();
});

</script>

<template>
    <NavbarTopMenu />

    <section class="min-h-screen bg-gray-100 dark:bg-gray-900 p-5">

        <!-- <Banner /> -->

        <!-- <div class="my-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-1 place-items-center"> -->
        <div class="my-5 flex flex-wrap items-start justify-center gap-6">
            <ProductCardHorizontal
                v-for="product in products"
                :key="product.id"
                :product="product"
            />
        </div>
    </section>
    <!-- <ProductCardDetails /> -->
</template>
