<script setup>
import { onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Header from './Header.vue';
import Footer from './Footer.vue';

const page = usePage();

onMounted(() => {
    // Cargar carrito si el usuario está autenticado
    if (page.props.auth?.user) {
        // TODO: fetchCart();
    }
});
</script>

<template>
    <div class="min-h-screen bg-white dark:bg-slate-900">
        <Header />

        <main class="min-h-screen">
            <slot />
        </main>

        <Footer />
    </div>
</template>

<style scoped>
/* Estilos específicos del layout si son necesarios */
</style>
