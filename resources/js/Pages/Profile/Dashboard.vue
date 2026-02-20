<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { route } from '@/Composables/useRoutes';

defineOptions({ layout: AuthenticatedLayout });

const page = usePage();
const user = computed(() => page.props.auth?.user);
const isAuthenticated = computed(() => !!user.value);

const loading = ref(false);

const stats = ref({
    totalOrders: 0,
    pendingOrders: 0,
    totalSpent: 0,
    completedOrders: 0
});

const recentOrders = ref([
    {
        id: '#ORD-2024-001',
        date: '2024-02-15',
        status: 'Completado',
        total: 89.99
    },
    {
        id: '#ORD-2024-002',
        date: '2024-02-18',
        status: 'Procesando',
        total: 125.50
    },
    {
        id: '#ORD-2024-003',
        date: '2024-02-20',
        status: 'Pendiente',
        total: 45.00
    },
    {
        id: '#ORD-2024-004',
        date: '2024-02-10',
        status: 'Completado',
        total: 67.80
    },
    {
        id: '#ORD-2024-005',
        date: '2024-02-05',
        status: 'Cancelado',
        total: 150.00
    }
]);

const userInitials = computed(() => {
    if (!user.value) return 'U';
    const name = user.value.name || '';
    const parts = name.trim().split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const options = {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    };
    return date.toLocaleDateString('es-ES', options);
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: 'EUR'
    }).format(price);
};

const getStatusColor = (status) => {
    const colors = {
        'Pendiente': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        'Procesando': 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
        'Completado': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        'Cancelado': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
    };
    return colors[status] || 'bg-slate-100 text-slate-800 dark:bg-slate-900/30 dark:text-slate-400';
};

const handleLogout = () => {
    if (confirm('¿Estás seguro de que quieres cerrar sesión?')) {
        loading.value = true;
        router.post(route('logout'), {}, {
            onFinish: () => {
                loading.value = false;
            }
        });
    }
};

onMounted(() => {
    if (!isAuthenticated.value) {
        router.visit(route('login'));
    }
});
</script>

<template>
    <Head>
        <title>Mi Cuenta - PawfectShop</title>
        <meta name="description" content="Gestiona tu cuenta y pedidos en PawfectShop" />
    </Head>

    <div class="min-h-screen bg-slate-50 dark:bg-slate-900 py-12 px-6 lg:px-20">
        <div class="max-w-7xl mx-auto">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-4xl font-extrabold mb-2">
                    Mi <span class="text-primary">Cuenta</span>
                </h1>
                <p class="text-slate-600 dark:text-slate-400">
                    Gestiona tu perfil, pedidos y preferencias
                </p>
            </div>

            <!-- User Header Card -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-lg p-8 mb-8">
                <div class="flex flex-col md:flex-row items-center gap-6">
                    <!-- Avatar -->
                    <div class="relative">
                        <div class="w-24 h-24 rounded-full bg-gradient-to-br from-primary to-orange-400 flex items-center justify-center text-white text-3xl font-bold shadow-lg">
                            <span v-if="user?.avatar">{{ userInitials }}</span>
                            <span v-else class="material-symbols-outlined text-4xl">person</span>
                        </div>
                        <div class="absolute -bottom-1 -right-1 w-8 h-8 bg-green-500 rounded-full border-4 border-white dark:border-slate-800"></div>
                    </div>

                    <!-- User Info -->
                    <div class="flex-1 text-center md:text-left">
                        <h2 class="text-2xl font-bold mb-1">
                            {{ user?.name || 'Usuario' }}
                        </h2>
                        <p class="text-slate-600 dark:text-slate-400 mb-2">
                            {{ user?.email || 'usuario@email.com' }}
                        </p>
                        <div class="flex items-center justify-center md:justify-start gap-2">
                            <span class="material-symbols-outlined text-primary text-xl">verified</span>
                            <span class="text-sm text-slate-500 dark:text-slate-500">Miembro desde 2024</span>
                        </div>
                    </div>

                    <!-- Edit Button -->
                    <Link
                        :href="route('profile.dashboard')"
                        class="px-6 py-3 bg-primary text-background-dark font-bold rounded-xl hover:scale-105 active:scale-95 transition-all shadow-lg shadow-primary/30 inline-flex items-center gap-2"
                    >
                        <span class="material-symbols-outlined">edit</span>
                        Editar Perfil
                    </Link>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Orders -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md p-6 hover:shadow-xl transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                            <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-2xl">shopping_bag</span>
                        </div>
                        <span class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ stats.totalOrders }}</span>
                    </div>
                    <h3 class="font-semibold text-slate-800 dark:text-slate-200">Total de Pedidos</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-500 mt-1">Todos tus pedidos</p>
                </div>

                <!-- Pending Orders -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md p-6 hover:shadow-xl transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/30 rounded-xl flex items-center justify-center">
                            <span class="material-symbols-outlined text-yellow-600 dark:text-yellow-400 text-2xl">pending</span>
                        </div>
                        <span class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ stats.pendingOrders }}</span>
                    </div>
                    <h3 class="font-semibold text-slate-800 dark:text-slate-200">Pedidos Pendientes</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-500 mt-1">En procesamiento</p>
                </div>

                <!-- Total Spent -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md p-6 hover:shadow-xl transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                            <span class="material-symbols-outlined text-green-600 dark:text-green-400 text-2xl">payments</span>
                        </div>
                        <span class="text-3xl font-bold text-green-600 dark:text-green-400">{{ formatPrice(stats.totalSpent) }}</span>
                    </div>
                    <h3 class="font-semibold text-slate-800 dark:text-slate-200">Total Gastado</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-500 mt-1">Acumulado total</p>
                </div>

                <!-- Completed Orders -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md p-6 hover:shadow-xl transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-primary/20 rounded-xl flex items-center justify-center">
                            <span class="material-symbols-outlined text-primary text-2xl">check_circle</span>
                        </div>
                        <span class="text-3xl font-bold text-primary">{{ stats.completedOrders }}</span>
                    </div>
                    <h3 class="font-semibold text-slate-800 dark:text-slate-200">Completados</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-500 mt-1">Entregados con éxito</p>
                </div>
            </div>

            <!-- Main Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Personal Information -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">person</span>
                                Información Personal
                            </h3>
                        </div>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between py-3 border-b border-slate-100 dark:border-slate-700">
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-slate-400">badge</span>
                                    <span class="text-slate-600 dark:text-slate-400">Nombre completo</span>
                                </div>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ user?.name || 'Usuario' }}</span>
                            </div>
                            <div class="flex items-center justify-between py-3 border-b border-slate-100 dark:border-slate-700">
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-slate-400">email</span>
                                    <span class="text-slate-600 dark:text-slate-400">Email</span>
                                </div>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ user?.email || 'usuario@email.com' }}</span>
                            </div>
                            <div class="flex items-center justify-between py-3 border-b border-slate-100 dark:border-slate-700">
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-slate-400">phone</span>
                                    <span class="text-slate-600 dark:text-slate-400">Teléfono</span>
                                </div>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ user?.phone || 'No configurado' }}</span>
                            </div>
                            <div class="flex items-center justify-between py-3">
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-slate-400">calendar_today</span>
                                    <span class="text-slate-600 dark:text-slate-400">Miembro desde</span>
                                </div>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Febrero 2024</span>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Orders -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">inventory_2</span>
                                Pedidos Recientes
                            </h3>
                            <Link
                                :href="route('profile.orders')"
                                class="text-primary hover:text-orange-500 font-semibold text-sm inline-flex items-center gap-1 transition-colors"
                            >
                                Ver todos
                                <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </Link>
                        </div>
                        <div class="space-y-4">
                            <div
                                v-for="order in recentOrders"
                                :key="order.id"
                                class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors"
                            >
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-slate-200 dark:bg-slate-700 rounded-lg flex items-center justify-center">
                                        <span class="material-symbols-outlined text-slate-500 dark:text-slate-400">shopping_bag</span>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-800 dark:text-slate-200">{{ order.id }}</p>
                                        <p class="text-sm text-slate-500 dark:text-slate-500">{{ formatDate(order.date) }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span
                                        :class="['px-3 py-1 rounded-full text-xs font-semibold', getStatusColor(order.status)]"
                                    >
                                        {{ order.status }}
                                    </span>
                                    <span class="font-bold text-slate-800 dark:text-slate-200 min-w-[80px] text-right">
                                        {{ formatPrice(order.total) }}
                                    </span>
                                    <button class="p-2 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg transition-colors">
                                        <span class="material-symbols-outlined text-slate-600 dark:text-slate-400">visibility</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-8">
                    <!-- Shipping Address -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">location_on</span>
                                Dirección de Envío
                            </h3>
                        </div>
                        <div class="mb-6">
                            <div class="bg-slate-50 dark:bg-slate-900/50 rounded-xl p-4">
                                <p class="font-semibold text-slate-800 dark:text-slate-200 mb-2">Casa</p>
                                <p class="text-slate-600 dark:text-slate-400 text-sm">
                                    Calle Principal 123<br/>
                                    Piso 2, Puerta A<br/>
                                    28001, Madrid, España
                                </p>
                            </div>
                        </div>
                        <button class="w-full px-4 py-3 border-2 border-primary text-primary font-bold rounded-xl hover:bg-primary hover:text-background-dark transition-all inline-flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined">edit</span>
                            Gestionar Direcciones
                        </button>
                    </div>

                    <!-- Quick Links -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md p-6">
                        <h3 class="text-xl font-bold flex items-center gap-2 mb-6">
                            <span class="material-symbols-outlined text-primary">link</span>
                            Enlaces Rápidos
                        </h3>
                        <div class="space-y-2">
                            <Link
                                :href="route('profile.orders')"
                                class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-900/50 transition-colors group"
                            >
                                <span class="material-symbols-outlined text-slate-400 group-hover:text-primary transition-colors">history</span>
                                <span class="text-slate-700 dark:text-slate-300 font-medium">Historial de Pedidos</span>
                                <span class="material-symbols-outlined text-slate-400 ml-auto">chevron_right</span>
                            </Link>
                            <Link
                                :href="route('profile.dashboard')"
                                class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-900/50 transition-colors group"
                            >
                                <span class="material-symbols-outlined text-slate-400 group-hover:text-primary transition-colors">favorite</span>
                                <span class="text-slate-700 dark:text-slate-300 font-medium">Lista de Deseos</span>
                                <span class="text-slate-400 text-sm ml-auto">Próximamente</span>
                            </Link>
                            <Link
                                :href="route('profile.dashboard')"
                                class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-900/50 transition-colors group"
                            >
                                <span class="material-symbols-outlined text-slate-400 group-hover:text-primary transition-colors">settings</span>
                                <span class="text-slate-700 dark:text-slate-300 font-medium">Configuración</span>
                                <span class="material-symbols-outlined text-slate-400 ml-auto">chevron_right</span>
                            </Link>
                            <button
                                @click="handleLogout"
                                :disabled="loading"
                                class="w-full flex items-center gap-3 p-3 rounded-xl hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors group text-left"
                            >
                                <span class="material-symbols-outlined text-slate-400 group-hover:text-red-500 transition-colors">logout</span>
                                <span class="text-slate-700 dark:text-slate-300 font-medium group-hover:text-red-500">Cerrar Sesión</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Transiciones suaves */
a, button {
    transition: all 0.2s ease;
}

/* Scrollbar personalizado para listas largas */
* {
    scrollbar-width: thin;
    scrollbar-color: rgb(203 213 225) transparent;
}

.dark * {
    scrollbar-color: rgb(51 65 85) transparent;
}
</style>
