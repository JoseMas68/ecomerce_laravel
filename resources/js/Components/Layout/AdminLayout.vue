<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { route } from '@/Composables/useRoutes';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const sidebarOpen = ref(false);

const handleLogout = () => {
    if (confirm('¿Cerrar sesión como administrador?')) {
        router.post(route('logout'));
    }
};
</script>

<template>
    <div class="h-screen flex overflow-hidden bg-slate-50 dark:bg-slate-900">
        <!-- Sidebar para Escritorio -->
        <aside class="hidden md:flex flex-col w-64 bg-slate-900 text-slate-300">
            <div class="h-16 flex items-center px-6 border-b border-slate-800">
                <Link :href="route('home')" class="text-xl font-bold text-white flex items-center gap-2 hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-primary">pets</span> PawfectShop
                </Link>
            </div>
            <div class="flex-1 overflow-y-auto py-4 custom-scrollbar">
                <nav class="px-3 space-y-1">
                    <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-4">Navegación</p>
                    <Link :href="route('admin.dashboard')" 
                        :class="page.url === '/admin' ? 'bg-slate-800 text-white border-l-4 border-primary' : 'hover:bg-slate-800 hover:text-white border-l-4 border-transparent'"
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-r-md transition-colors">
                        <span class="material-symbols-outlined mr-3" :class="page.url === '/admin' ? 'text-primary' : 'text-slate-400 group-hover:text-slate-300'">dashboard</span>
                        Dashboard
                    </Link>
                    
                    <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-6">Catálogo</p>
                    <Link :href="route('admin.products.index')" 
                        :class="page.url.startsWith('/admin/products') ? 'bg-slate-800 text-white border-l-4 border-primary' : 'hover:bg-slate-800 hover:text-white border-l-4 border-transparent'"
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-r-md transition-colors">
                        <span class="material-symbols-outlined mr-3" :class="page.url.startsWith('/admin/products') ? 'text-primary' : 'text-slate-400 group-hover:text-slate-300'">inventory_2</span>
                        Productos
                    </Link>
                </nav>
            </div>
            
            <div class="p-4 border-t border-slate-800">
                <button @click="handleLogout" class="flex items-center w-full px-3 py-2 text-sm font-medium text-red-400 hover:bg-slate-800 hover:text-red-300 rounded-md transition-colors">
                    <span class="material-symbols-outlined mr-3">logout</span>
                    Cerrar Sesión
                </button>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Header Móvil & Desktop Superior -->
            <header class="bg-white dark:bg-slate-800 shadow-sm z-10">
                <div class="px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                    <!-- Boton menu movil (ignorado para el layout de admin simple por ahora) -->
                    <div class="flex items-center md:hidden">
                        <span class="text-lg font-bold">Admin Mobile</span>
                    </div>
                    
                    <!-- Search bar (placeholder) -->
                    <div class="flex-1 px-4 flex justify-start hidden md:flex">
                        <div class="w-full max-w-lg relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                            <input class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-full pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-primary outline-none text-slate-900 dark:text-white" placeholder="Buscar pedidos, productos..." type="search">
                        </div>
                    </div>

                    <!-- Right Topbar -->
                    <div class="ml-4 flex items-center md:ml-6 gap-4">
                        <Link :href="route('home')" class="text-sm font-medium text-slate-500 hover:text-primary transition-colors flex items-center gap-1 hidden sm:flex">
                            <span class="material-symbols-outlined text-lg">storefront</span> Volver a la Tienda
                        </Link>

                        <!-- Panel Perfil Admin -->
                        <div class="relative flex items-center gap-3 pl-4 border-l border-slate-200 dark:border-slate-700">
                            <div class="text-right hidden md:block">
                                <p class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ user?.name || 'Administrador' }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Admin</p>
                            </div>
                            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-primary to-green-500 flex items-center justify-center text-white font-bold shadow-md">
                                A
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Panel Content -->
            <main class="flex-1 relative overflow-y-auto focus:outline-none bg-slate-50 dark:bg-slate-900">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #334155;
    border-radius: 4px;
}
</style>
