<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Components/Layout/AdminLayout.vue';
import { route } from '@/Composables/useRoutes';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    product: {
        type: Object,
        default: null
    },
    categories: {
        type: Array,
        required: true
    },
    brands: {
        type: Array,
        required: true
    }
});

const isEditing = computed(() => !!props.product);

const form = useForm({
    name: props.product?.name || '',
    slug: props.product?.slug || '',
    sku: props.product?.sku || '',
    description: props.product?.description || '',
    price: props.product?.price || 0,
    compare_at_price: props.product?.compare_at_price || null,
    cost: props.product?.cost || null,
    stock: props.product?.stock || 0,
    brand_id: props.product?.brand_id || '',
    category_id: props.product?.category_id || '',
    is_active: props.product?.is_active ?? true,
    image_url: props.product?.image_url || '',
});

// Auto-generate slug from name if slug is empty
const generateSlug = () => {
    if (!form.slug && form.name) {
        form.slug = form.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
    }
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('admin.products.update', props.product.id));
    } else {
        form.post(route('admin.products.store'));
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Editar Producto - Admin' : 'Crear Producto - Admin'" />

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto">
        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <Link :href="route('admin.products.index')" class="w-10 h-10 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-500 hover:text-primary transition-colors">
                    <span class="material-symbols-outlined">arrow_back</span>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                        {{ isEditing ? 'Editar Producto' : 'Añadir Nuevo Producto' }}
                    </h1>
                    <p class="text-sm text-slate-500">{{ isEditing ? product.name : 'Completa los detalles para agregarlo al catálogo' }}</p>
                </div>
            </div>
            
            <button @click="submit" :disabled="form.processing" class="bg-primary text-background-dark px-6 py-2.5 rounded-xl font-bold hover:bg-[#5cdb42] transition shadow-lg shadow-primary/20 flex items-center gap-2 disabled:opacity-50">
                <span v-if="form.processing" class="material-symbols-outlined animate-spin text-sm">sync</span>
                <span v-else class="material-symbols-outlined text-sm">save</span>
                {{ isEditing ? 'Actualizar Producto' : 'Guardar Producto' }}
            </button>
        </div>

        <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Detalles Principales -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Información General -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-6">Información General</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Nombre del Producto *</label>
                            <input v-model="form.name" @blur="generateSlug" type="text" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-transparent outline-none text-slate-900 dark:text-white">
                            <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Descripción</label>
                            <textarea v-model="form.description" rows="5" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-transparent outline-none text-slate-900 dark:text-white resize-y"></textarea>
                            <div v-if="form.errors.description" class="text-red-500 text-xs mt-1">{{ form.errors.description }}</div>
                        </div>
                    </div>
                </div>

                <!-- Precios -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-6">Precio y Costes</h2>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Precio de Venta (€) *</label>
                            <input v-model="form.price" type="number" step="0.01" min="0" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-transparent outline-none text-slate-900 dark:text-white">
                            <div v-if="form.errors.price" class="text-red-500 text-xs mt-1">{{ form.errors.price }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Precio Anterior (Tachado)</label>
                            <input v-model="form.compare_at_price" type="number" step="0.01" min="0" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-transparent outline-none text-slate-900 dark:text-white">
                            <div v-if="form.errors.compare_at_price" class="text-red-500 text-xs mt-1">{{ form.errors.compare_at_price }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Coste de Compra</label>
                            <input v-model="form.cost" type="number" step="0.01" min="0" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-transparent outline-none text-slate-900 dark:text-white">
                        </div>
                    </div>
                </div>

                <!-- Media -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-6">Multimedia</h2>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">URL de Imagen Principal</label>
                        <input v-model="form.image_url" type="url" placeholder="https://..." class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-transparent outline-none text-slate-900 dark:text-white">
                        <div v-if="form.errors.image_url" class="text-red-500 text-xs mt-1">{{ form.errors.image_url }}</div>
                        
                        <!-- Image Preview -->
                        <div v-if="form.image_url" class="mt-4 w-32 h-32 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700">
                            <img :src="form.image_url" class="w-full h-full object-cover">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Organización -->
            <div class="space-y-6">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-6">Organización</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Estado</label>
                            <label class="flex items-center cursor-pointer">
                                <div class="relative">
                                    <input type="checkbox" v-model="form.is_active" class="sr-only">
                                    <div class="block bg-slate-200 dark:bg-slate-700 w-14 h-8 rounded-full transition-colors" :class="{'bg-green-500': form.is_active}"></div>
                                    <div class="dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition-transform" :class="{'transform translate-x-6': form.is_active}"></div>
                                </div>
                                <div class="ml-3 text-sm font-bold" :class="form.is_active ? 'text-green-600 dark:text-green-400' : 'text-slate-500'">
                                    {{ form.is_active ? 'Activo en Tienda' : 'Borrador / Oculto' }}
                                </div>
                            </label>
                        </div>

                        <hr class="border-slate-100 dark:border-slate-700 my-4">

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Categoría *</label>
                            <select v-model="form.category_id" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-transparent outline-none text-slate-900 dark:text-white">
                                <option value="" disabled>Selecciona una categoría</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                            <div v-if="form.errors.category_id" class="text-red-500 text-xs mt-1">{{ form.errors.category_id }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Marca *</label>
                            <select v-model="form.brand_id" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-transparent outline-none text-slate-900 dark:text-white">
                                <option value="" disabled>Selecciona una marca</option>
                                <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
                            </select>
                            <div v-if="form.errors.brand_id" class="text-red-500 text-xs mt-1">{{ form.errors.brand_id }}</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-6">Inventario</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">SKU (Código Interno) *</label>
                            <input v-model="form.sku" type="text" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-transparent outline-none text-slate-900 dark:text-white uppercase font-mono">
                            <div v-if="form.errors.sku" class="text-red-500 text-xs mt-1">{{ form.errors.sku }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Stock Disponible *</label>
                            <input v-model="form.stock" type="number" min="0" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-transparent outline-none text-slate-900 dark:text-white font-bold text-lg">
                            <div v-if="form.errors.stock" class="text-red-500 text-xs mt-1">{{ form.errors.stock }}</div>
                        </div>
                        
                        <div class="hidden">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Slug URL *</label>
                            <input v-model="form.slug" type="text" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-primary outline-none">
                            <div v-if="form.errors.slug" class="text-red-500 text-xs mt-1">{{ form.errors.slug }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>
