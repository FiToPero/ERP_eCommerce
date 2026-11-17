<script setup>
import { ref, onMounted } from 'vue';
import ProductCardHorizontal from '../Components/ProductCardHorizontal.vue';
import ProductCardDetails from '../Components/ProductCardDetails.vue';
import NavbarTopMenu from '../Components/NavbarTopMenu.vue';

const API_URL = import.meta.env.VITE_API_URL;
const products = ref([]);
const selectedProduct = ref(null);
const categories = ref([]);

onMounted(async () => {
    const response = await fetch(`${API_URL}/products/index`);
    products.value = await response.json();

    const categoryResponse = await fetch(`${API_URL}/categories/index`);
    categories.value = await categoryResponse.json();
});

</script>

<template>
    <NavbarTopMenu :categories="categories" />

    <section class="min-h-screen bg-gray-100 dark:bg-gray-900 p-5">

        <!-- <Banner /> -->

        <div class="my-5 flex flex-wrap items-start justify-center gap-6">
            <ProductCardHorizontal
                v-for="product in products"
                :key="product.id"
                :product="product"
                @select="selectedProduct = $event"
            />
        </div>
    </section>
    <ProductCardDetails
        v-if="selectedProduct"
        :product="selectedProduct"
        @close="selectedProduct = null"
    />
</template>
