<script setup>
import { ref, onMounted } from 'vue';
import ProductCardVertical from '@/Components/ProductCardVertical.vue';
import ProductCardDetails from '../Components/ProductCardDetails.vue';
import Banner from '../Components/Banner.vue';
import NavbarTopMenu from '../Components/NavbarTopMenu.vue';
// Este import centraliza la URL de la API para el catálogo público de la SPA.
import { defaultApiOrigin } from '@/config/runtimeUrls';

// Esta constante usa el origen de la API por dominio y evita caer al puerto 8090 antiguo.
const API_URL = import.meta.env.VITE_API_URL ?? defaultApiOrigin;
const products = ref([]);
const selectedProduct = ref(null);

onMounted(async () => {
    const response = await fetch(`${API_URL}/products/index`);
    products.value = await response.json();
});

</script>

<template>
    <NavbarTopMenu />

    <section class="min-h-screen bg-gray-100 dark:bg-gray-900 p-5">

        <!-- <Banner /> -->

        <!-- <div class="my-5 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5"> -->
        <div class="my-5 flex flex-wrap items-start justify-center gap-6">
            <ProductCardVertical
                v-for="product in products"
                :key="product.id"
                :product="product"
                @select="selectedProduct = $event"
            />
                            <!--
                    @select="selectedProduct = $event"
                    @ es una abreviación de v-on.
                    Se utiliza para escuchar eventos.
                    Aquí escuchamos un evento llamado select
                    que debe emitir el componente hijo.
                    Cuando ProductCardVertical emite ese evento,
                    ejecutamos:
                    selectedProduct = $event
                    $event representa el valor enviado por el
                    componente hijo mediante emit().
                    Ejemplo en ProductCardVertical.vue:
                    const emit = defineEmits(['select']);
                    function selectProduct() {
                        emit('select', props.product);
                    }
                    Cuando se ejecuta emit(), el producto llega
                    al componente padre mediante $event.
                    Así almacenamos el producto seleccionado
                    en nuestra variable reactiva selectedProduct.
                -->
        </div>
        <!-- <div class="my-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-1 place-items-center"> -->
        <div class="my-5 flex flex-wrap items-start justify-center gap-6">

        </div>
    </section>
    <ProductCardDetails
        v-if="selectedProduct"
        :product="selectedProduct"
        @close="selectedProduct = null"
    />
          <!--
            @close="selectedProduct = null"
            Escuchamos un evento llamado close.
            Este evento debe ser emitido por ProductCardDetails.
            Ejemplo dentro de ProductCardDetails.vue:
            const emit = defineEmits(['close']);
            function closeDetails() {
                emit('close');
            }
            Cuando el componente hijo emite close,
            el componente padre ejecuta:
            selectedProduct = null
            Como selectedProduct vuelve a ser null,
            la condición del v-if deja de cumplirse.
            Por lo tanto, Vue elimina ProductCardDetails del DOM.
            Esto permite cerrar la ventana de detalles del producto.
        -->
</template>



 
