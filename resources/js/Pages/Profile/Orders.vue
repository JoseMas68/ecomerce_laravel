<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Link, Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { useAuth } from '@/Composables/useAuth';
import { route } from '@/Composables/useRoutes';

defineOptions({ layout: AuthenticatedLayout });

const { user, isAuthenticated } = useAuth();

// Estado reactivo
const orders = ref([]);
const loading = ref(false);

const filters = ref({
    status: 'all',
    date: 'all',
    search: '',
    sort: 'newest'
});

const pagination = ref({
    currentPage: 1,
    perPage: 10,
    total: 0
});

// Datos placeholder
const placeholderOrders = [
    {
        id: 1234,
        date: '2026-02-15',
        status: 'completed',
        items: [
            {
                id: 1,
                name: 'Comida Premium para Perros Adultos',
                quantity: 2,
                price: 45.99,
                image: '/images/products/dog-food.jpg'
            },
            {
                id: 2,
                name: 'Juguete Interactivo para Mascotas',
                quantity: 1,
                price: 34.01,
                image: '/images/products/toy.jpg'
            }
        ],
        total: 125.99,
        shipping_address: 'Calle Principal 123, Madrid'
    },
    {
        id: 1233,
        date: '2026-02-10',
        status: 'shipped',
        items: [
            {
                id: 3,
                name: 'Cama Ortopédica para Perros',
                quantity: 1,
                price: 89.50,
                image: '/images/products/bed.jpg'
            }
        ],
        total: 89.50,
        shipping_address: 'Avenida Libertad 45, Barcelona'
    },
    {
        id: 1232,
        date: '2026-02-05',
        status: 'processing',
        items: [
            {
                id: 4,
                name: 'Correa Retráctil para Perros',
                quantity: 1,
                price: 25.00,
                image: '/images/products/leash.jpg'
            },
            {
                id: 5,
                name: 'Collar Ajustable',
                quantity: 1,
                price: 20.00,
                image: '/images/products/collar.jpg'
            }
        ],
        total: 45.00,
        shipping_address: 'Plaza Mayor 8, Sevilla'
    },
    {
        id: 1231,
        date: '2026-02-01',
        status: 'cancelled',
        items: [
            {
                id: 6,
                name: 'Transportadora para Mascotas Grande',
                quantity: 1,
                price: 210.00,
                image: '/images/products/carrier.jpg'
            }
        ],
        total: 210.00,
        shipping_address: 'Calle Luna 23, Valencia'
    },
    {
        id: 1230,
        date: '2026-01-28',
        status: 'delivered',
        items: [
            {
                id: 7,
                name: 'Set de Cepillo y Peine para Gatos',
                quantity: 1,
                price: 35.00,
                image: '/images/products/brush.jpg'
            },
            {
                id: 8,
                name: 'Rascador para Gatos',
                quantity: 1,
                price: 32.25,
                image: '/images/products/scratcher.jpg'
            }
        ],
        total: 67.25,
        shipping_address: 'Calle Sol 15, Bilbao'
    }
];

// Estados disponibles
const statusOptions = [
    { value: 'all', label: 'Todos los estados' },
    { value: 'pending', label: 'Pendiente' },
    { value: 'processing', label: 'Procesando' },
    { value: 'shipped', label: 'Enviado' },
    { value: 'delivered', label: 'Entregado' },
    { value: 'cancelled', label: 'Cancelado' }
];

const dateOptions = [
    { value: 'all', label: 'Todo el historial' },
    { value: 'month', label: 'Último mes' },
    { value: 'three_months', label: 'Últimos 3 meses' },
    { value: 'year', label: 'Último año' }
];

const sortOptions = [
    { value: 'newest', label: 'Más recientes' },
    { value: 'oldest', label: 'Más antiguos' },
    { value: 'highest', label: 'Mayor monto' },
    { value: 'lowest', label: 'Menor monto' }
];

// Obtener pedidos
const fetchOrders = async () => {
    loading.value = true;
    try {
        // TODO: Implementar llamada a la API
        // const response = await axios.get('/api/v1/orders', {
        //     params: {
        //         page: pagination.value.currentPage,
        //         per_page: pagination.value.perPage,
        //         status: filters.value.status,
        //         search: filters.value.search
        //     }
        // });

        // Simular carga con datos placeholder
        await new Promise(resolve => setTimeout(resolve, 500));
        orders.value = placeholderOrders;
        pagination.value.total = 45;

    } catch (error) {
        console.error('Error al cargar pedidos:', error);
    } finally {
        loading.value = false;
    }
};

// Filtrar pedidos
const filteredOrders = computed(() => {
    let filtered = [...orders.value];

    // Filtrar por estado
    if (filters.value.status !== 'all') {
        filtered = filtered.filter(order => order.status === filters.value.status);
    }

    // Filtrar por fecha
    if (filters.value.date !== 'all') {
        const now = new Date();
        const orderDate = (dateString) => new Date(dateString);

        filtered = filtered.filter(order => {
            const orderDateTime = orderDate(order.date);
            const diffTime = Math.abs(now - orderDateTime);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            switch (filters.value.date) {
                case 'month':
                    return diffDays <= 30;
                case 'three_months':
                    return diffDays <= 90;
                case 'year':
                    return diffDays <= 365;
                default:
                    return true;
            }
        });
    }

    // Buscar por ID
    if (filters.value.search) {
        const searchTerm = filters.value.search.toLowerCase();
        filtered = filtered.filter(order =>
            order.id.toString().includes(searchTerm)
        );
    }

    // Ordenar
    switch (filters.value.sort) {
        case 'newest':
            filtered.sort((a, b) => new Date(b.date) - new Date(a.date));
            break;
        case 'oldest':
            filtered.sort((a, b) => new Date(a.date) - new Date(b.date));
            break;
        case 'highest':
            filtered.sort((a, b) => b.total - a.total);
            break;
        case 'lowest':
            filtered.sort((a, b) => a.total - b.total);
            break;
    }

    return filtered;
});

// Pedidos paginados
const paginatedOrders = computed(() => {
    const start = (pagination.value.currentPage - 1) * pagination.value.perPage;
    const end = start + pagination.value.perPage;
    return filteredOrders.value.slice(start, end);
});

// Calcular total de páginas
const totalPages = computed(() => {
    return Math.ceil(filteredOrders.value.length / pagination.value.perPage);
});

// Info de paginación
const paginationInfo = computed(() => {
    const start = (pagination.value.currentPage - 1) * pagination.value.perPage + 1;
    const end = Math.min(start + pagination.value.perPage - 1, filteredOrders.value.length);
    return `Mostrando ${start}-${end} de ${filteredOrders.value.length} pedidos`;
});

// Formatear fecha
const formatDate = (dateString) => {
    const date = new Date(dateString);
    const options = { day: 'numeric', month: 'short', year: 'numeric' };
    return date.toLocaleDateString('es-ES', options);
};

// Formatear precio
const formatPrice = (price) => {
    return new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: 'EUR'
    }).format(price);
};

// Obtener color del estado
const getStatusColor = (status) => {
    const colors = {
        pending: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
        processing: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
        shipped: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
        delivered: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        completed: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
    };
    return colors[status] || 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-400';
};

// Obtener etiqueta del estado
const getStatusLabel = (status) => {
    const labels = {
        pending: 'Pendiente',
        processing: 'Procesando',
        shipped: 'Enviado',
        delivered: 'Entregado',
        completed: 'Completado',
        cancelled: 'Cancelado'
    };
    return labels[status] || status;
};

// Ver detalle del pedido
const viewOrderDetail = (orderId) => {
    router.visit(route('profile.orders.detail', { id: orderId }));
};

// Rastrear pedido
const trackOrder = (orderId) => {
    router.visit(route('profile.orders.track', { id: orderId }));
};

// Reordenar
const reorderOrder = (orderId) => {
    // TODO: Implementar funcionalidad de reordenar
    console.log('Reordenar pedido:', orderId);
};

// Paginación
const nextPage = () => {
    if (pagination.value.currentPage < totalPages.value) {
        pagination.value.currentPage++;
    }
};

const prevPage = () => {
    if (pagination.value.currentPage > 1) {
        pagination.value.currentPage--;
    }
};

const goToPage = (page) => {
    pagination.value.currentPage = page;
};

// Aplicar filtros
const applyFilters = () => {
    pagination.value.currentPage = 1;
};

// Limpiar filtros
const clearFilters = () => {
    filters.value = {
        status: 'all',
        date: 'all',
        search: '',
        sort: 'newest'
    };
    pagination.value.currentPage = 1;
};

// Verificar si hay filtros activos
const hasActiveFilters = computed(() => {
    return filters.value.status !== 'all' ||
           filters.value.date !== 'all' ||
           filters.value.search !== '';
});

// Verificar si puede reordenar
const canReorder = (status) => {
    return status === 'delivered' || status === 'completed';
};

// Cargar datos al montar
onMounted(() => {
    if (!isAuthenticated.value) {
        router.visit(route('login'));
        return;
    }
    fetchOrders();
});

// Watch para cambiar de página
watch(() => pagination.value.currentPage, () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});
</script>

<template>
    <Head>
        <title>Mis Pedidos - PawfectShop</title>
        <meta name="description" content="Consulta y gestiona todos tus pedidos en PawfectShop." />
    </Head>

    <!-- Breadcrumbs -->
    <div class="max-w-7xl mx-auto px-6 lg:px-20 pt-8 pb-4">
        <div class="flex items-center gap-2 text-sm text-slate-500">
            <Link :href="route('home')" class="hover:text-primary transition-colors">
                Inicio
            </Link>
            <span class="material-symbols-outlined text-xs">chevron_right</span>
            <Link :href="route('profile.dashboard')" class="hover:text-primary transition-colors">
                Mi Cuenta
            </Link>
            <span class="material-symbols-outlined text-xs">chevron_right</span>
            <span class="font-medium text-slate-900 dark:text-slate-100">Mis Pedidos</span>
        </div>
    </div>

    <!-- Page Header -->
    <div class="max-w-7xl mx-auto px-6 lg:px-20 pb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-4xl font-extrabold">
                    Mis <span class="text-primary">Pedidos</span>
                </h1>
                <p class="text-slate-600 dark:text-slate-400 mt-2 text-lg">
                    Consulta y gestiona el historial de tus compras
                </p>
            </div>
            <Link
                :href="route('catalog.index')"
                class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-background-dark font-bold rounded-lg hover:opacity-90 transition-opacity"
            >
                <span class="material-symbols-outlined">shopping_bag</span>
                Seguir Comprando
            </Link>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-6 lg:px-20 pb-20">

        <!-- Filters Section -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 mb-8 shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex flex-col lg:flex-row gap-6">

                <!-- Search -->
                <div class="flex-1">
                    <label class="block text-sm font-semibold mb-2">Buscar por ID</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                            search
                        </span>
                        <input
                            v-model="filters.search"
                            @keyup.enter="applyFilters"
                            type="text"
                            placeholder="#1234"
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-100 dark:bg-slate-900 rounded-lg text-sm focus:ring-2 focus:ring-primary/50 border-none"
                        />
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="lg:w-48">
                    <label class="block text-sm font-semibold mb-2">Estado</label>
                    <select
                        v-model="filters.status"
                        @change="applyFilters"
                        class="w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-900 rounded-lg text-sm focus:ring-2 focus:ring-primary/50 border-none"
                    >
                        <option
                            v-for="status in statusOptions"
                            :key="status.value"
                            :value="status.value"
                        >
                            {{ status.label }}
                        </option>
                    </select>
                </div>

                <!-- Date Filter -->
                <div class="lg:w-56">
                    <label class="block text-sm font-semibold mb-2">Periodo</label>
                    <select
                        v-model="filters.date"
                        @change="applyFilters"
                        class="w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-900 rounded-lg text-sm focus:ring-2 focus:ring-primary/50 border-none"
                    >
                        <option
                            v-for="date in dateOptions"
                            :key="date.value"
                            :value="date.value"
                        >
                            {{ date.label }}
                        </option>
                    </select>
                </div>

                <!-- Sort -->
                <div class="lg:w-56">
                    <label class="block text-sm font-semibold mb-2">Ordenar por</label>
                    <select
                        v-model="filters.sort"
                        class="w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-900 rounded-lg text-sm focus:ring-2 focus:ring-primary/50 border-none"
                    >
                        <option
                            v-for="sort in sortOptions"
                            :key="sort.value"
                            :value="sort.value"
                        >
                            {{ sort.label }}
                        </option>
                    </select>
                </div>

                <!-- Clear Filters -->
                <div class="lg:w-auto flex items-end">
                    <button
                        v-if="hasActiveFilters"
                        @click="clearFilters"
                        class="w-full lg:w-auto px-6 py-2.5 text-primary hover:bg-primary/10 rounded-lg transition-colors font-semibold text-sm flex items-center justify-center gap-2"
                    >
                        <span class="material-symbols-outlined text-lg">filter_alt_off</span>
                        Limpiar Filtros
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="space-y-6">
            <div v-for="i in 3" :key="i" class="bg-white dark:bg-slate-800 rounded-2xl p-6 animate-pulse">
                <div class="flex flex-col lg:flex-row lg:items-center gap-6">
                    <div class="flex-1 space-y-3">
                        <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-32"></div>
                        <div class="h-3 bg-slate-200 dark:bg-slate-700 rounded w-48"></div>
                    </div>
                    <div class="h-6 bg-slate-200 dark:bg-slate-700 rounded-full w-24"></div>
                    <div class="h-5 bg-slate-200 dark:bg-slate-700 rounded w-20"></div>
                    <div class="flex gap-2">
                        <div class="h-10 bg-slate-200 dark:bg-slate-700 rounded-lg w-24"></div>
                        <div class="h-10 bg-slate-200 dark:bg-slate-700 rounded-lg w-24"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders List - Desktop Table -->
        <div v-else-if="paginatedOrders.length > 0" class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden hidden lg:block">
            <table class="w-full">
                <thead class="bg-slate-50 dark:bg-slate-900/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Pedido</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Items</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    <tr
                        v-for="order in paginatedOrders"
                        :key="order.id"
                        class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors"
                    >
                        <td class="px-6 py-4">
                            <span class="font-bold text-slate-900 dark:text-slate-100">#{{ order.id }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                            {{ formatDate(order.date) }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex -space-x-2">
                                    <div
                                        v-for="(item, index) in order.items.slice(0, 3)"
                                        :key="index"
                                        class="w-10 h-10 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center border-2 border-white dark:border-slate-800"
                                    >
                                        <span class="material-symbols-outlined text-lg text-slate-500">image</span>
                                    </div>
                                    <div
                                        v-if="order.items.length > 3"
                                        class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center text-xs font-bold border-2 border-white dark:border-slate-800"
                                    >
                                        +{{ order.items.length - 3 }}
                                    </div>
                                </div>
                                <div class="text-sm">
                                    <p class="font-medium text-slate-900 dark:text-slate-100">{{ order.items[0].name }}</p>
                                    <p v-if="order.items.length > 1" class="text-slate-500">
                                        +{{ order.items.length - 1 }} más
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold"
                                :class="getStatusColor(order.status)"
                            >
                                <span
                                    class="w-1.5 h-1.5 rounded-full"
                                    :class="getStatusColor(order.status).split(' ')[0]"
                                ></span>
                                {{ getStatusLabel(order.status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="font-bold text-lg text-slate-900 dark:text-slate-100">
                                {{ formatPrice(order.total) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <button
                                    @click="viewOrderDetail(order.id)"
                                    class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors group"
                                    title="Ver detalle"
                                >
                                    <span class="material-symbols-outlined text-slate-500 group-hover:text-primary">visibility</span>
                                </button>
                                <button
                                    v-if="order.status === 'shipped' || order.status === 'processing'"
                                    @click="trackOrder(order.id)"
                                    class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors group"
                                    title="Rastrear pedido"
                                >
                                    <span class="material-symbols-outlined text-slate-500 group-hover:text-primary">local_shipping</span>
                                </button>
                                <button
                                    v-if="canReorder(order.status)"
                                    @click="reorderOrder(order.id)"
                                    class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors group"
                                    title="Comprar de nuevo"
                                >
                                    <span class="material-symbols-outlined text-slate-500 group-hover:text-primary">reorder</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Orders List - Mobile Cards -->
        <div v-else-if="paginatedOrders.length > 0" class="space-y-4 lg:hidden">
            <div
                v-for="order in paginatedOrders"
                :key="order.id"
                class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-slate-700"
            >
                <!-- Header -->
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <span class="font-bold text-lg text-slate-900 dark:text-slate-100">#{{ order.id }}</span>
                        <p class="text-sm text-slate-500 mt-1">{{ formatDate(order.date) }}</p>
                    </div>
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold"
                        :class="getStatusColor(order.status)"
                    >
                        <span
                            class="w-1.5 h-1.5 rounded-full"
                            :class="getStatusColor(order.status).split(' ')[0]"
                        ></span>
                        {{ getStatusLabel(order.status) }}
                    </span>
                </div>

                <!-- Items -->
                <div class="mb-4">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="flex -space-x-2">
                            <div
                                v-for="(item, index) in order.items.slice(0, 3)"
                                :key="index"
                                class="w-12 h-12 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center border-2 border-white dark:border-slate-800"
                            >
                                <span class="material-symbols-outlined text-xl text-slate-500">image</span>
                            </div>
                            <div
                                v-if="order.items.length > 3"
                                class="w-12 h-12 rounded-full bg-primary text-white flex items-center justify-center text-sm font-bold border-2 border-white dark:border-slate-800"
                            >
                                +{{ order.items.length - 3 }}
                            </div>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-slate-900 dark:text-slate-100">{{ order.items[0].name }}</p>
                            <p v-if="order.items.length > 1" class="text-sm text-slate-500">
                                +{{ order.items.length - 1 }} items más
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Total and Actions -->
                <div class="flex items-center justify-between pt-4 border-t border-slate-200 dark:border-slate-700">
                    <div>
                        <p class="text-sm text-slate-500">Total</p>
                        <p class="font-bold text-xl text-slate-900 dark:text-slate-100">
                            {{ formatPrice(order.total) }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <button
                            @click="viewOrderDetail(order.id)"
                            class="px-4 py-2 bg-slate-100 dark:bg-slate-700 rounded-lg text-sm font-semibold hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors flex items-center gap-1"
                        >
                            <span class="material-symbols-outlined text-lg">visibility</span>
                            Ver
                        </button>
                        <button
                            v-if="order.status === 'shipped' || order.status === 'processing'"
                            @click="trackOrder(order.id)"
                            class="p-2 bg-slate-100 dark:bg-slate-700 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors"
                        >
                            <span class="material-symbols-outlined text-lg">local_shipping</span>
                        </button>
                        <button
                            v-if="canReorder(order.status)"
                            @click="reorderOrder(order.id)"
                            class="p-2 bg-primary text-white rounded-lg hover:opacity-90 transition-opacity"
                        >
                            <span class="material-symbols-outlined text-lg">reorder</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white dark:bg-slate-800 rounded-2xl p-12 text-center shadow-sm border border-slate-200 dark:border-slate-700">
            <span class="material-symbols-outlined text-6xl text-slate-300">shopping_bag</span>
            <h3 class="text-xl font-bold mt-4 mb-2 text-slate-900 dark:text-slate-100">
                No se encontraron pedidos
            </h3>
            <p class="text-slate-500 mb-6">
                {{ hasActiveFilters ? 'Intenta ajustar tus filtros de búsqueda' : 'Aún no has realizado ninguna compra' }}
            </p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <button
                    v-if="hasActiveFilters"
                    @click="clearFilters"
                    class="px-6 py-3 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors"
                >
                    Limpiar Filtros
                </button>
                <Link
                    :href="route('catalog.index')"
                    class="px-6 py-3 bg-primary text-background-dark font-semibold rounded-lg hover:opacity-90 transition-opacity inline-flex items-center justify-center gap-2"
                >
                    <span class="material-symbols-outlined">shopping_bag</span>
                    Explorar Productos
                </Link>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1 && !loading" class="flex flex-col sm:flex-row justify-between items-center gap-4 mt-8">
            <p class="text-sm text-slate-600 dark:text-slate-400">
                {{ paginationInfo }}
            </p>
            <div class="flex items-center gap-2">
                <button
                    @click="prevPage"
                    :disabled="pagination.currentPage === 1"
                    class="p-2 rounded-lg bg-white dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors border border-slate-200 dark:border-slate-700"
                >
                    <span class="material-symbols-outlined">chevron_left</span>
                </button>

                <div class="flex gap-1">
                    <button
                        v-for="page in Math.min(totalPages, 5)"
                        :key="page"
                        @click="goToPage(page)"
                        class="w-10 h-10 rounded-lg font-semibold transition-colors text-sm"
                        :class="pagination.currentPage === page
                            ? 'bg-primary text-background-dark'
                            : 'bg-white dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700'"
                    >
                        {{ page }}
                    </button>
                    <span
                        v-if="totalPages > 5"
                        class="flex items-center px-2 text-slate-500"
                    >
                        ...
                    </span>
                    <button
                        v-if="totalPages > 5"
                        @click="goToPage(totalPages)"
                        class="w-10 h-10 rounded-lg font-semibold transition-colors bg-white dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 text-sm"
                    >
                        {{ totalPages }}
                    </button>
                </div>

                <button
                    @click="nextPage"
                    :disabled="pagination.currentPage === totalPages"
                    class="p-2 rounded-lg bg-white dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors border border-slate-200 dark:border-slate-700"
                >
                    <span class="material-symbols-outlined">chevron_right</span>
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Transiciones suaves */
tr, .bg-white {
    transition: all 0.2s ease-in-out;
}

/* Smooth scroll */
html {
    scroll-behavior: smooth;
}
</style>
