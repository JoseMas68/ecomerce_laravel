<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import ProductCard from '@/Components/Catalog/ProductCard.vue';
import { Link, Head } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    initialCategory: String,
    initialBrand: String,
    initialMinPrice: [String, Number],
    initialMaxPrice: [String, Number],
    initialSearch: String
});

// Estado
const products = ref([]);
const categories = ref([]);
const brands = ref([]);
const loading = ref(true);
const currentPage = ref(1);
const totalPages = ref(1);

// Filtros
const filters = ref({
    category: props.initialCategory || null,
    brand: props.initialBrand || null,
    minPrice: props.initialMinPrice || null,
    maxPrice: props.initialMaxPrice || null,
    search: props.initialSearch || '',
    sort: 'featured',
    perPage: 12
});

// Cargar productos
const fetchProducts = async () => {
    loading.value = true;
    try {
        const params = {
            page: currentPage.value,
            sort: filters.value.sort,
            per_page: filters.value.perPage
        };

        if (filters.value.category) params.category_id = filters.value.category;
        if (filters.value.brand) params.brand_id = filters.value.brand;
        if (filters.value.minPrice) params.min_price = filters.value.minPrice;
        if (filters.value.maxPrice) params.max_price = filters.value.maxPrice;
        if (filters.value.search) params.search = filters.value.search;

        const response = await axios.get('/api/v1/products', { params });

        if (response.data.success) {
            products.value = response.data.data.data || response.data.data;
            totalPages.value = response.data.data.last_page || 1;
        }
    } catch (error) {
        console.error('Error al cargar productos:', error);
    } finally {
        loading.value = false;
    }
};

// Cargar categorías
const fetchCategories = async () => {
    try {
        const response = await axios.get('/api/v1/categories');
        if (response.data.success) {
            categories.value = response.data.data.data || response.data.data;
        }
    } catch (error) {
        console.error('Error al cargar categorías:', error);
    }
};

// Cargar marcas
const fetchBrands = async () => {
    try {
        const response = await axios.get('/api/v1/brands');
        if (response.data.success) {
            brands.value = response.data.data.data || response.data.data;
        }
    } catch (error) {
        console.error('Error al cargar marcas:', error);
    }
};

// Aplicar filtros
const applyFilters = () => {
    currentPage.value = 1;
    fetchProducts();
};

// Limpiar filtros
const clearFilters = () => {
    filters.value = {
        category: null,
        brand: null,
        minPrice: null,
        maxPrice: null,
        search: '',
        sort: 'featured',
        perPage: 12
    };
    currentPage.value = 1;
    fetchProducts();
};

// Paginación
const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
        fetchProducts();
    }
};

const prevPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
        fetchProducts();
    }
};

const goToPage = (page) => {
    currentPage.value = page;
    fetchProducts();
};

// Watch filters
watch(() => filters.value.sort, () => {
    currentPage.value = 1;
    fetchProducts();
});

// Formatear precio
const formatPrice = (price) => {
    return new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: 'EUR'
    }).format(price);
};

// Cargar datos al montar
onMounted(async () => {
    await Promise.all([
        fetchProducts(),
        fetchCategories(),
        fetchBrands()
    ]);
});

// Computed
const hasActiveFilters = computed(() => {
    return !!(filters.value.category || filters.value.brand ||
             filters.value.minPrice || filters.value.maxPrice ||
             filters.value.search);
});
</script>

<template>
    <Head>
        <title>Catálogo - PawfectShop</title>
        <meta name="description" content="Explora nuestro catálogo completo de productos para mascotas." />
    </Head>

    <!-- Breadcrumbs -->
    <div class="max-w-7xl mx-auto px-6 lg:px-20 pt-8 pb-4">
        <div class="flex items-center gap-2 text-sm text-slate-500">
            <Link :href="route('home')" class="hover:text-primary transition-colors">
                Inicio
            </Link>
            <span class="material-symbols-outlined text-xs">chevron_right</span>
            <span class="font-medium text-slate-900 dark:text-slate-100">Catálogo</span>
        </div>
    </div>

    <!-- Page Header -->
    <div class="max-w-7xl mx-auto px-6 lg:px-20 pb-12">
        <h1 class="text-4xl font-extrabold">
            Nuestros <span class="text-primary">Productos</span>
        </h1>
        <p class="text-slate-600 dark:text-slate-400 mt-2 text-lg">
            Encuentra todo lo que necesitas para tu mascota
        </p>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-6 lg:px-20 pb-20">
        <div class="flex flex-col lg:flex-row gap-12">

            <!-- Sidebar Filters -->
            <aside class="w-full lg:w-72 shrink-0 space-y-8">
                <!-- Clear Filters -->
                <div v-if="hasActiveFilters" class="flex items-center justify-between p-4 bg-white dark:bg-slate-800 rounded-xl">
                    <span class="text-sm font-semibold">Filtros activos</span>
                    <button
                        @click="clearFilters"
                        class="text-sm text-primary hover:underline"
                    >
                        Limpiar todos
                    </button>
                </div>

                <!-- Search -->
                <div>
                    <h3 class="font-bold mb-4">Buscar</h3>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                            search
                        </span>
                        <input
                            v-model="filters.search"
                            @keyup.enter="applyFilters"
                            type="text"
                            placeholder="Buscar productos..."
                            class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-slate-800 rounded-lg text-sm focus:ring-2 focus:ring-primary/50"
                        />
                    </div>
                </div>

                <!-- Categories -->
                <div>
                    <h3 class="font-bold mb-4">Categorías</h3>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 cursor-pointer hover:text-primary transition-colors">
                            <input
                                v-model="filters.category"
                                :value="null"
                                type="radio"
                                @change="applyFilters"
                                class="text-primary focus:ring-primary"
                            />
                            <span class="text-sm">Todas</span>
                        </label>
                        <label
                            v-for="category in categories"
                            :key="category.id"
                            class="flex items-center justify-between gap-2 cursor-pointer hover:text-primary transition-colors"
                        >
                            <div class="flex items-center gap-2">
                                <input
                                    v-model="filters.category"
                                    :value="category.id"
                                    type="radio"
                                    @change="applyFilters"
                                    class="text-primary focus:ring-primary"
                                />
                                <span class="text-sm">{{ category.name }}</span>
                            </div>
                            <span class="text-xs text-slate-500">({{ category.products_count || 0 }})</span>
                        </label>
                    </div>
                </div>

                <!-- Brands -->
                <div>
                    <h3 class="font-bold mb-4">Marcas</h3>
                    <div class="space-y-2 max-h-60 overflow-y-auto">
                        <label class="flex items-center gap-2 cursor-pointer hover:text-primary transition-colors">
                            <input
                                v-model="filters.brand"
                                :value="null"
                                type="radio"
                                @change="applyFilters"
                                class="text-primary focus:ring-primary"
                            />
                            <span class="text-sm">Todas</span>
                        </label>
                        <label
                            v-for="brand in brands"
                            :key="brand.id"
                            class="flex items-center gap-2 cursor-pointer hover:text-primary transition-colors"
                        >
                            <input
                                v-model="filters.brand"
                                :value="brand.id"
                                type="radio"
                                @change="applyFilters"
                                class="text-primary focus:ring-primary"
                            />
                            <span class="text-sm">{{ brand.name }}</span>
                        </label>
                    </div>
                </div>

                <!-- Price Range -->
                <div>
                    <h3 class="font-bold mb-4">Rango de Precio</h3>
                    <div class="space-y-3">
                        <div class="flex items-center gap-2">
                            <input
                                v-model="filters.minPrice"
                                type="number"
                                placeholder="Mín"
                                class="w-full px-3 py-2 bg-white dark:bg-slate-800 rounded-lg text-sm"
                            />
                            <span class="text-slate-500">-</span>
                            <input
                                v-model="filters.maxPrice"
                                type="number"
                                placeholder="Máx"
                                class="w-full px-3 py-2 bg-white dark:bg-slate-800 rounded-lg text-sm"
                            />
                        </div>
                        <button
                            @click="applyFilters"
                            class="w-full py-2 bg-slate-900 dark:bg-primary text-white dark:text-background-dark text-sm font-semibold rounded-lg hover:opacity-90 transition-opacity"
                        >
                            Aplicar
                        </button>
                    </div>
                </div>
            </aside>

            <!-- Products Grid -->
            <div class="flex-1">
                <!-- Toolbar -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                    <p class="text-slate-600 dark:text-slate-400">
                        Mostrando <span class="font-semibold">{{ products.length }}</span> productos
                    </p>

                    <div class="flex items-center gap-4">
                        <label class="text-sm font-semibold">Ordenar por:</label>
                        <select
                            v-model="filters.sort"
                            class="px-4 py-2 bg-white dark:bg-slate-800 rounded-lg text-sm focus:ring-2 focus:ring-primary/50 border-none"
                        >
                            <option value="featured">Destacados</option>
                            <option value="price_asc">Precio: Menor a Mayor</option>
                            <option value="price_desc">Precio: Mayor a Menor</option>
                            <option value="newest">Más Recientes</option>
                            <option value="name_asc">Nombre A-Z</option>
                        </select>
                    </div>
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="i in 6" :key="i" class="animate-pulse">
                        <div class="bg-slate-200 dark:bg-slate-800 rounded-2xl aspect-square mb-4"></div>
                        <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded mb-2"></div>
                        <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded w-2/3"></div>
                    </div>
                </div>

                <!-- Products -->
                <div v-else-if="products.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    <ProductCard
                        v-for="product in products"
                        :key="product.id"
                        :product="product"
                    />
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-20">
                    <span class="material-symbols-outlined text-6xl text-slate-300">search_off</span>
                    <h3 class="text-xl font-bold mt-4 mb-2">No se encontraron productos</h3>
                    <p class="text-slate-500 mb-6">Intenta ajustar tus filtros de búsqueda</p>
                    <button
                        @click="clearFilters"
                        class="px-6 py-3 bg-primary text-background-dark font-semibold rounded-lg hover:opacity-90 transition-opacity"
                    >
                        Limpiar Filtros
                    </button>
                </div>

                <!-- Pagination -->
                <div v-if="totalPages > 1 && !loading" class="flex justify-center items-center gap-2 mt-12">
                    <button
                        @click="prevPage"
                        :disabled="currentPage === 1"
                        class="p-2 rounded-lg bg-white dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <span class="material-symbols-outlined">chevron_left</span>
                    </button>

                    <div class="flex gap-2">
                        <button
                            v-for="page in Math.min(totalPages, 5)"
                            :key="page"
                            @click="goToPage(page)"
                            class="w-10 h-10 rounded-lg font-semibold transition-colors"
                            :class="currentPage === page
                                ? 'bg-primary text-background-dark'
                                : 'bg-white dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700'"
                        >
                            {{ page }}
                        </button>
                    </div>

                    <button
                        @click="nextPage"
                        :disabled="currentPage === totalPages"
                        class="p-2 rounded-lg bg-white dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <span class="material-symbols-outlined">chevron_right</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
