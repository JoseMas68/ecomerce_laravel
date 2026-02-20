<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { route } from '@/Composables/useRoutes';

const page = usePage();
const cartCount = computed(() => page.props.cartCount || 0);
const user = computed(() => page.props.auth?.user);

const isSearchOpen = ref(false);
const searchQuery = ref('');

const handleSearch = () => {
    if (searchQuery.value.trim()) {
        // Navegar a la página de búsqueda
        // Inertia.visit(`/catalog?search=${searchQuery.value}`);
    }
};
</script>

<template>
    <header class="sticky top-0 z-50 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 px-4 sm:px-6 lg:px-8 py-3">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between gap-2">
                <!-- Logo y Navegación Desktop -->
                <div class="flex items-center gap-4 lg:gap-6 flex-1">
                    <Link :href="route('home')" class="flex items-center gap-2 shrink-0">
                        <div class="bg-primary p-1.5 rounded-lg text-white">
                            <span class="material-symbols-outlined !text-xl block">pets</span>
                        </div>
                        <span class="text-lg sm:text-xl font-extrabold tracking-tight hidden sm:block">PawfectShop</span>
                    </Link>

                    <nav class="hidden md:flex items-center gap-4 lg:gap-6 text-xs sm:text-sm font-semibold text-slate-600 dark:text-slate-400">
                        <Link :href="route('catalog.index')"
                              class="hover:text-primary transition-colors whitespace-nowrap">
                            Tienda
                        </Link>
                        <a href="#" class="hover:text-primary transition-colors whitespace-nowrap">Cuidado</a>
                        <a href="#" class="hover:text-primary transition-colors whitespace-nowrap">Ofertas</a>
                        <a href="#" class="hover:text-primary transition-colors whitespace-nowrap hidden lg:block">Nosotros</a>
                    </nav>
                </div>

                <!-- Search, Wishlist, Cart, User -->
                <div class="flex items-center gap-2 sm:gap-3">
                    <!-- Search Bar (Desktop) -->
                    <div class="relative hidden sm:block">
                        <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">
                            search
                        </span>
                        <input
                            v-model="searchQuery"
                            @keyup.enter="handleSearch"
                            type="text"
                            placeholder="Buscar..."
                            class="pl-8 pr-3 py-1.5 bg-slate-100 dark:bg-slate-800 border-none rounded-full text-xs sm:text-sm w-36 lg:w-48 focus:ring-2 focus:ring-primary/50 focus:w-44 lg:focus:w-56 transition-all"
                        />
                    </div>

                    <!-- Search Button (Mobile) -->
                    <button class="sm:hidden p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full">
                        <span class="material-symbols-outlined text-xl">search</span>
                    </button>

                    <!-- Wishlist -->
                    <button class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full hidden sm:block">
                        <span class="material-symbols-outlined text-xl">favorite</span>
                    </button>

                    <!-- Cart -->
                    <Link :href="route('cart.index')" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full relative">
                        <span class="material-symbols-outlined text-xl">shopping_cart</span>
                        <span v-if="cartCount > 0"
                              class="absolute top-0 right-0 bg-primary text-white text-[9px] font-bold w-4 h-4 flex items-center justify-center rounded-full">
                            {{ cartCount > 9 ? '9+' : cartCount }}
                        </span>
                    </Link>

                    <!-- User Logged In -->
                    <template v-if="user">
                        <!-- User Menu Desktop -->
                        <div class="relative group hidden sm:block">
                            <button class="w-8 h-8 rounded-full bg-primary/20 border border-primary/20 flex items-center justify-center">
                                <span class="material-symbols-outlined text-primary text-lg">person</span>
                            </button>
                            <!-- Dropdown -->
                            <div class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-900 rounded-lg shadow-lg border border-slate-200 dark:border-slate-800 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all">
                                <Link :href="route('profile.dashboard')"
                                      class="block px-4 py-2 text-sm hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                    Mi Perfil
                                </Link>
                                <Link :href="route('profile.orders')"
                                      class="block px-4 py-2 text-sm hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                    Mis Pedidos
                                </Link>
                                <hr class="border-slate-200 dark:border-slate-800">
                                <Link :href="route('logout')"
                                      method="post"
                                      class="block px-4 py-2 text-sm text-red-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                    Cerrar Sesión
                                </Link>
                            </div>
                        </div>

                        <!-- User Button Mobile -->
                        <Link :href="route('profile.dashboard')" class="sm:hidden p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full">
                            <span class="material-symbols-outlined text-xl">person</span>
                        </Link>
                    </template>

                    <!-- Guest Login/Register -->
                    <template v-else>
                        <!-- Desktop -->
                        <div class="hidden lg:flex items-center gap-2">
                            <Link :href="route('login')"
                                  class="px-3 py-1.5 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:text-primary transition-colors">
                                Iniciar
                            </Link>
                            <Link :href="route('register')"
                                  class="px-3 py-1.5 bg-primary text-background-dark text-sm font-bold rounded-lg hover:opacity-90 transition-opacity">
                                Registrarse
                            </Link>
                        </div>

                        <!-- Mobile -->
                        <div class="flex lg:hidden items-center gap-1">
                            <Link :href="route('login')"
                                  class="px-2 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-primary transition-colors">
                                Iniciar
                            </Link>
                            <Link :href="route('register')"
                                  class="p-1.5 bg-primary text-background-dark rounded-lg hover:opacity-90 transition-opacity">
                                <span class="material-symbols-outlined !text-lg">person_add</span>
                            </Link>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </header>
</template>

<style scoped>
/* Estilos específicos si son necesarios */
</style>
