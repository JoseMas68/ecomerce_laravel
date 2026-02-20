<script setup>
import { ref, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { useCart } from '@/Composables/useCart';
import { route } from '@/Composables/useRoutes';

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    product: {
        type: Object,
        required: true
    },
    related_products: {
        type: Array,
        default: () => []
    }
});

const { addToCart, loading: cartLoading } = useCart();
const page = usePage();

const quantity = ref(1);
const mainImage = ref(props.product.image_url || 'https://images.unsplash.com/photo-1583337130417-3346a1be7dee?auto=format&fit=crop&q=80&w=800&h=800');

// Galería simulada si no hay múltiples imágenes (placeholder para el diseño)
const gallery = ref([
    mainImage.value,
    'https://images.unsplash.com/photo-1560807707-8cc77767d783?auto=format&fit=crop&q=80&w=800&h=800',
    'https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?auto=format&fit=crop&q=80&w=800&h=800',
    'https://images.unsplash.com/photo-1520560205263-2284c8a5a408?auto=format&fit=crop&q=80&w=800&h=800'
]);

const increaseQuantity = () => {
    if (quantity.value < props.product.stock) quantity.value++;
};

const decreaseQuantity = () => {
    if (quantity.value > 1) quantity.value--;
};

const handleAddToCart = async () => {
    await addToCart(props.product.id, quantity.value);
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: 'EUR'
    }).format(price || 0);
};

const discountPercentage = computed(() => {
    if (!props.product.compare_at_price || props.product.compare_at_price <= props.product.price) return 0;
    return Math.round(((props.product.compare_at_price - props.product.price) / props.product.compare_at_price) * 100);
});
</script>

<template>
    <Head>
        <title>{{ product.name }} - PawfectShop</title>
        <meta name="description" :content="product.description" />
    </Head>

    <div class="min-h-screen bg-slate-50 dark:bg-slate-900 py-8 lg:py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            
            <!-- Breadcrumbs -->
            <nav class="flex text-sm text-slate-500 dark:text-slate-400 mb-8" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2">
                    <li>
                        <Link :href="route('home')" class="hover:text-primary transition-colors">Inicio</Link>
                    </li>
                    <li><span class="material-symbols-outlined text-sm mx-1">chevron_right</span></li>
                    <li>
                        <Link :href="route('catalog.index')" class="hover:text-primary transition-colors">Catálogo</Link>
                    </li>
                    <li><span class="material-symbols-outlined text-sm mx-1">chevron_right</span></li>
                    <li v-if="product.category">
                        <Link :href="route('catalog.index') + '?category=' + product.category.slug" class="hover:text-primary transition-colors">{{ product.category.name }}</Link>
                    </li>
                    <li v-if="product.category"><span class="material-symbols-outlined text-sm mx-1">chevron_right</span></li>
                    <li class="text-slate-900 dark:text-slate-100 font-medium" aria-current="page">{{ product.name }}</li>
                </ol>
            </nav>

            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-xl overflow-hidden mb-12">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
                    
                    <!-- Left Column: Product Gallery -->
                    <div class="p-6 lg:p-10 border-b lg:border-b-0 lg:border-r border-slate-100 dark:border-slate-700">
                        <div class="aspect-square rounded-2xl overflow-hidden mb-4 relative bg-slate-100 dark:bg-slate-900">
                            <!-- Discount Badge -->
                            <div v-if="discountPercentage > 0" class="absolute top-4 left-4 bg-red-500 text-white px-3 py-1 rounded-full text-sm font-bold shadow-lg z-10">
                                -{{ discountPercentage }}%
                            </div>
                            
                            <!-- Main Image -->
                            <img 
                                :src="mainImage" 
                                :alt="product.name"
                                class="w-full h-full object-cover transition-transform duration-500 hover:scale-105"
                            />
                        </div>
                        
                        <!-- Thumbnail Gallery -->
                        <div class="grid grid-cols-4 gap-4">
                            <button 
                                v-for="(img, index) in gallery" 
                                :key="index"
                                @click="mainImage = img"
                                class="aspect-square rounded-xl overflow-hidden border-2 transition-all"
                                :class="mainImage === img ? 'border-primary shadow-md' : 'border-transparent hover:border-primary/50'"
                            >
                                <img :src="img" :alt="`${product.name} ${index + 1}`" class="w-full h-full object-cover opacity-80 hover:opacity-100">
                            </button>
                        </div>
                    </div>

                    <!-- Right Column: Product Details -->
                    <div class="p-6 lg:p-12 flex flex-col justify-center">
                        <!-- Brand -->
                        <div v-if="product.brand" class="mb-3">
                            <span class="text-primary font-bold tracking-wider uppercase text-sm">
                                {{ product.brand.name }}
                            </span>
                        </div>
                        
                        <!-- Title -->
                        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 dark:text-white leading-tight mb-4">
                            {{ product.name }}
                        </h1>
                        
                        <!-- Ratings (Simulated) & SKU -->
                        <div class="flex items-center flex-wrap gap-4 mb-6">
                            <div class="flex items-center text-yellow-500">
                                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
                                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
                                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
                                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
                                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star_half</span>
                                <span class="text-slate-500 dark:text-slate-400 text-sm ml-2">(128 opiniones)</span>
                            </div>
                            <div class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></div>
                            <span class="text-sm text-slate-500 dark:text-slate-400">SKU: {{ product.sku }}</span>
                        </div>
                        
                        <!-- Price -->
                        <div class="flex items-end gap-3 mb-8">
                            <span class="text-4xl sm:text-5xl font-extrabold text-primary">
                                {{ formatPrice(product.price) }}
                            </span>
                            <span v-if="product.compare_at_price && product.compare_at_price > product.price" class="text-xl sm:text-2xl text-slate-400 line-through mb-1">
                                {{ formatPrice(product.compare_at_price) }}
                            </span>
                        </div>
                        
                        <!-- Description -->
                        <div class="prose prose-slate dark:prose-invert max-w-none mb-10 text-slate-600 dark:text-slate-300" v-html="product.description">
                        </div>
                        
                        <div class="border-t border-slate-200 dark:border-slate-700 pt-8 mt-auto">
                            <!-- Stock Status -->
                            <div class="flex items-center gap-2 mb-6 text-sm font-semibold">
                                <span class="relative flex h-3 w-3">
                                  <span v-if="product.stock > 0" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                  <span class="relative inline-flex rounded-full h-3 w-3" :class="product.stock > 0 ? 'bg-green-500' : 'bg-red-500'"></span>
                                </span>
                                <span :class="product.stock > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                    {{ product.stock > 0 ? `En Stock (${product.stock} disponibles)` : 'Agotado' }}
                                </span>
                            </div>

                            <!-- Add to Cart Widget -->
                            <div class="flex flex-col sm:flex-row gap-4">
                                <!-- Quantity -->
                                <div class="flex items-center border-2 border-slate-200 dark:border-slate-600 rounded-2xl bg-white dark:bg-slate-800 h-14 w-full sm:w-36">
                                    <button 
                                        @click="decreaseQuantity" 
                                        :disabled="quantity <= 1 || product.stock === 0"
                                        class="px-4 py-2 hover:text-primary disabled:opacity-50 h-full w-full flex items-center justify-center transition-colors"
                                    >
                                        <span class="material-symbols-outlined">remove</span>
                                    </button>
                                    <span class="font-bold text-lg w-full text-center">{{ quantity }}</span>
                                    <button 
                                        @click="increaseQuantity" 
                                        :disabled="quantity >= product.stock || product.stock === 0"
                                        class="px-4 py-2 hover:text-primary disabled:opacity-50 h-full w-full flex items-center justify-center transition-colors"
                                    >
                                        <span class="material-symbols-outlined">add</span>
                                    </button>
                                </div>
                                
                                <!-- Add Button -->
                                <button 
                                    @click="handleAddToCart"
                                    :disabled="product.stock === 0 || cartLoading"
                                    class="flex-1 bg-primary text-background-dark font-extrabold rounded-2xl py-4 px-6 flex items-center justify-center gap-2 hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 transition-all shadow-lg shadow-primary/30 h-14"
                                >
                                    <span v-if="!cartLoading" class="material-symbols-outlined text-background-dark">shopping_cart</span>
                                    <span v-else class="material-symbols-outlined animate-spin text-background-dark">sync</span>
                                    <span>{{ cartLoading ? 'Añadiendo...' : 'Añadir al Carrito' }}</span>
                                </button>
                                
                                <!-- Favorite Button -->
                                <button class="h-14 w-14 border-2 border-slate-200 dark:border-slate-600 rounded-2xl flex items-center justify-center text-slate-400 hover:text-red-500 hover:border-red-500 transition-colors bg-white dark:bg-slate-800">
                                    <span class="material-symbols-outlined text-xl">favorite</span>
                                </button>
                            </div>
                        </div>

                        <!-- Benefits/Features list -->
                        <div class="grid grid-cols-2 gap-4 mt-8 pt-8 border-t border-slate-200 dark:border-slate-700">
                            <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                                <span class="material-symbols-outlined text-emerald-500 bg-emerald-100 dark:bg-emerald-900/30 p-1.5 rounded-lg text-lg">local_shipping</span>
                                Envío gratis <span class="hidden xl:inline">desde 50€</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                                <span class="material-symbols-outlined text-blue-500 bg-blue-100 dark:bg-blue-900/30 p-1.5 rounded-lg text-lg">verified</span>
                                Calidad garantizada
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Related Products -->
            <div v-if="related_products.length > 0" class="mt-20">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-2xl lg:text-3xl font-extrabold text-slate-900 dark:text-white">Productos Relacionados</h2>
                    <Link :href="route('catalog.index') + '?category=' + product.category.slug" class="text-primary font-bold hover:underline hidden sm:inline-flex items-center gap-1">
                        Ver más <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </Link>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Iterating over related products but we will just pass them as props to ProductCard once component is made -->
                    <!-- In a real world, we would use ProductCard component. I'll simulate one for now to keep it standalone if ProductCard isn't perfect -->
                    
                    <div v-for="related in related_products" :key="related.id" class="bg-white dark:bg-slate-800 rounded-2xl shadow-md overflow-hidden group hover:shadow-xl transition-all duration-300">
                        <!-- Related Product Content -->
                        <Link :href="route('catalog.show', related.slug)">
                            <div class="aspect-w-1 aspect-h-1 relative bg-slate-100 dark:bg-slate-700 overflow-hidden">
                                <img :src="related.image_url || 'https://images.unsplash.com/photo-1583337130417-3346a1be7dee'" :alt="related.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            </div>
                            <div class="p-5">
                                <div class="text-xs text-primary font-bold mb-1 uppercase tracking-wider">{{ related.brand?.name || product.brand?.name }}</div>
                                <h3 class="font-bold text-slate-900 dark:text-white line-clamp-2 h-12 mb-2 group-hover:text-primary transition-colors">{{ related.name }}</h3>
                                <div class="flex items-end gap-2 mt-auto">
                                    <span class="text-xl font-extrabold">{{ formatPrice(related.price) }}</span>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>
