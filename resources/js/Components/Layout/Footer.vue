<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const email = ref('');

const subscribeNewsletter = () => {
    if (email.value) {
        // TODO: Implementar newsletter subscription
        console.log('Subscribe:', email.value);
        email.value = '';
    }
};
</script>

<template>
    <footer class="bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800 py-20 px-6 lg:px-20 mt-20">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
            <!-- Logo y Descripción -->
            <div class="space-y-6">
                <Link :href="route('home')" class="flex items-center gap-2">
                    <div class="bg-primary p-1.5 rounded-lg text-white">
                        <span class="material-symbols-outlined block">pets</span>
                    </div>
                    <span class="text-xl font-extrabold tracking-tight">PawfectShop</span>
                </Link>
                <p class="text-slate-500 text-sm leading-relaxed">
                    Haciendo que las colas meneen desde 2024. Tu tienda de productos para mascotas con consejos de expertos.
                </p>
                <div class="flex gap-4">
                    <a href="#" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 hover:bg-primary hover:text-white transition-all">
                        <span class="material-symbols-outlined text-lg">share</span>
                    </a>
                    <a href="#" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 hover:bg-primary hover:text-white transition-all">
                        <span class="material-symbols-outlined text-lg">public</span>
                    </a>
                    <a href="#" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 hover:bg-primary hover:text-white transition-all">
                        <span class="material-symbols-outlined text-lg">mail</span>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="font-bold mb-6">Enlaces Rápidos</h4>
                <ul class="space-y-4 text-sm text-slate-500">
                    <li>
                        <a href="#" class="hover:text-primary transition-colors">Rastrear Pedido</a>
                    </li>
                    <li>
                        <a href="#" class="hover:text-primary transition-colors">Buscador de Tiendas</a>
                    </li>
                    <li>
                        <a href="#" class="hover:text-primary transition-colors">Adopción de Mascotas</a>
                    </li>
                    <li>
                        <a href="#" class="hover:text-primary transition-colors">Blog</a>
                    </li>
                </ul>
            </div>

            <!-- Support -->
            <div>
                <h4 class="font-bold mb-6">Soporte</h4>
                <ul class="space-y-4 text-sm text-slate-500">
                    <li>
                        <a href="#" class="hover:text-primary transition-colors">Devoluciones y Reembolsos</a>
                    </li>
                    <li>
                        <a href="#" class="hover:text-primary transition-colors">Información de Envío</a>
                    </li>
                    <li>
                        <a href="#" class="hover:text-primary transition-colors">Tarjetas de Regalo</a>
                    </li>
                    <li>
                        <a href="#" class="hover:text-primary transition-colors">Contáctanos</a>
                    </li>
                </ul>
            </div>

            <!-- Newsletter -->
            <div>
                <h4 class="font-bold mb-6">Boletín</h4>
                <p class="text-sm text-slate-500 mb-4">
                    Suscríbete para recibir consejos de cuidado animal y ofertas exclusivas.
                </p>
                <div class="flex gap-2">
                    <input
                        v-model="email"
                        type="email"
                        placeholder="Correo electrónico"
                        class="bg-slate-100 dark:bg-slate-800 border-none rounded-lg px-4 py-2 text-sm flex-1 focus:ring-2 focus:ring-primary/50"
                    />
                    <button
                        @click="subscribeNewsletter"
                        class="bg-primary text-background-dark p-2 rounded-lg hover:opacity-90 transition-opacity"
                    >
                        <span class="material-symbols-outlined">send</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Copyright -->
        <div class="max-w-7xl mx-auto pt-12 mt-12 border-t border-slate-100 dark:border-slate-800 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-slate-400">
            <p>© 2024 PawfectShop Inc. Todos los derechos reservados.</p>
            <div class="flex gap-6">
                <a href="#" class="hover:text-primary transition-colors">Política de Privacidad</a>
                <a href="#" class="hover:text-primary transition-colors">Términos de Servicio</a>
                <a href="#" class="hover:text-primary transition-colors">Configuración de Cookies</a>
            </div>
        </div>
    </footer>
</template>

<style scoped>
/* Estilos específicos si son necesarios */
</style>
