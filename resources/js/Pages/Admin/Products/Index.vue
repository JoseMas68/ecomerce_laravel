<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Components/Layout/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    products: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({ search: '', status: '' })
    }
});

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');

const handleSearch = () => {
    router.get(route('admin.products.index'), {
        search: search.value,
        status: statusFilter.value
    }, { preserveState: true, preserveScroll: true, replace: true });
};

const deleteProduct = (id) => {
    if (confirm('¿Estás seguro de que deseas eliminar este producto permanentemente?')) {
        router.delete(route('admin.products.destroy', id), { preserveScroll: true });
    }
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: 'EUR'
    }).format(price || 0);
};
</script>

<template>
    <Head title="Gestión de Productos - Admin" />

    <div class="py-6 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">inventory_2</span>
                    Productos
                </h1>
                <p class="text-sm text-slate-500">Gestiona el catálogo, precios y stock de tu tienda</p>
            </div>
            
            <Link :href="route('admin.products.create')" class="bg-primary text-background-dark px-5 py-2.5 rounded-xl font-bold hover:bg-[#5cdb42] transition shadow-lg shadow-primary/20 flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-sm">add</span> Nuevo Producto
            </Link>
        </div>

        <!-- Toolbar / Filtros -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-4 mb-6 flex flex-col md:flex-row gap-4 items-center justify-between">
            
            <div class="flex-1 w-full flex items-center gap-4">
                <div class="relative max-w-md w-full">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                    <input 
                        v-model="search"
                        @input="handleSearch"
                        type="search" 
                        placeholder="Buscar por nombre, SKU..." 
                        class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none text-sm"
                    >
                </div>
                
                <select 
                    v-model="statusFilter"
                    @change="handleSearch"
                    class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-primary outline-none"
                >
                    <option value="">Todos los estados</option>
                    <option value="active">Activos</option>
                    <option value="draft">Borrador</option>
                </select>
            </div>
            
            <div class="text-sm text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900 px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700">
                Total: <strong class="text-slate-900 dark:text-white">{{ products.total }}</strong> productos
            </div>
            
        </div>

        <!-- Tabla -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[800px]">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider">
                            <th class="p-4 border-b border-slate-100 dark:border-slate-700 w-16">Imagen</th>
                            <th class="p-4 border-b border-slate-100 dark:border-slate-700">Producto</th>
                            <th class="p-4 border-b border-slate-100 dark:border-slate-700">SKU</th>
                            <th class="p-4 border-b border-slate-100 dark:border-slate-700">Precio</th>
                            <th class="p-4 border-b border-slate-100 dark:border-slate-700">Stock</th>
                            <th class="p-4 border-b border-slate-100 dark:border-slate-700">Estado</th>
                            <th class="p-4 border-b border-slate-100 dark:border-slate-700 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        
                        <tr v-if="products.data.length === 0">
                            <td colspan="7" class="p-8 text-center text-slate-500">
                                No se encontraron productos.
                            </td>
                        </tr>
                        
                        <tr v-for="product in products.data" :key="product.id" class="border-b border-slate-100 dark:border-slate-700 last:border-0 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition">
                            <td class="p-4">
                                <div class="w-12 h-12 rounded-lg object-cover bg-slate-100 dark:bg-slate-700 flex items-center justify-center overflow-hidden">
                                    <img v-if="product.image_url" :src="product.image_url" :alt="product.name" class="w-full h-full object-cover">
                                    <span v-else class="material-symbols-outlined text-slate-400">image</span>
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="font-bold text-slate-900 dark:text-white line-clamp-1 max-w-[250px]" :title="product.name">{{ product.name }}</div>
                                <div class="text-xs text-primary font-semibold mt-1">{{ product.category?.name || 'Sin Categoría' }}</div>
                            </td>
                            <td class="p-4 text-slate-500 font-mono text-xs">{{ product.sku }}</td>
                            <td class="p-4 font-bold text-slate-900 dark:text-white">{{ formatPrice(product.price) }}</td>
                            <td class="p-4">
                                <div class="flex items-center gap-1.5" :class="product.stock > 10 ? 'text-green-600' : (product.stock > 0 ? 'text-orange-500' : 'text-red-500')">
                                    <span v-if="product.stock > 10" class="material-symbols-outlined text-sm">check_circle</span>
                                    <span v-else-if="product.stock > 0" class="material-symbols-outlined text-sm">warning</span>
                                    <span v-else class="material-symbols-outlined text-sm">error</span>
                                    <span class="font-bold">{{ product.stock }}</span>
                                </div>
                            </td>
                            <td class="p-4">
                                <span v-if="product.is_active" class="px-2.5 py-1 bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-400 rounded-full text-xs font-bold ring-1 ring-inset ring-green-600/20">
                                    Activo
                                </span>
                                <span v-else class="px-2.5 py-1 bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300 rounded-full text-xs font-bold ring-1 ring-inset ring-slate-500/20">
                                    Borrador
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a :href="route('catalog.show', product.slug)" target="_blank" class="p-2 text-slate-400 hover:text-blue-500 transition-colors bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg shadow-sm" title="Ver en tienda">
                                        <span class="material-symbols-outlined text-sm">visibility</span>
                                    </a>
                                    <Link :href="route('admin.products.edit', product.id)" class="p-2 text-slate-400 hover:text-primary transition-colors bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg shadow-sm" title="Editar">
                                        <span class="material-symbols-outlined text-sm">edit</span>
                                    </Link>
                                    <button @click="deleteProduct(product.id)" class="p-2 text-slate-400 hover:text-red-500 transition-colors bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg shadow-sm" title="Eliminar">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination Controls (Inertia paginator integration) -->
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between" v-if="products.links && products.last_page > 1">
                <div class="text-sm text-slate-500 dark:text-slate-400 font-medium">
                    Mostrando del <span class="font-bold text-slate-900 dark:text-white">{{ products.from }}</span> 
                    al <span class="font-bold text-slate-900 dark:text-white">{{ products.to }}</span> 
                    de <span class="font-bold text-slate-900 dark:text-white">{{ products.total }}</span> resultados
                </div>
                
                <div class="flex gap-1">
                    <Link 
                        v-for="(link, i) in products.links" 
                        :key="i"
                        :href="link.url || '#'" 
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-sm font-medium transition"
                        :class="[
                            !link.url ? 'text-slate-400 cursor-not-allowed hidden md:flex' : '',
                            link.active ? 'bg-primary text-background-dark font-bold' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-600',
                            (i === 0 || i === products.links.length - 1) ? 'px-2 w-auto' : ''
                        ]"
                        v-html="link.label.replace('Previous', '&laquo;').replace('Next', '&raquo;')"
                        @click.prevent="!link.url ? null : router.visit(link.url)"
                    ></Link>
                </div>
            </div>
        </div>
        
    </div>
</template>
