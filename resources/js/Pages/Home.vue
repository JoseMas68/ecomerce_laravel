<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import ProductCard from '@/Components/Catalog/ProductCard.vue';
import { Link, Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';

defineOptions({ layout: AuthenticatedLayout });

const featuredProducts = ref([]);
const loading = ref(true);

onMounted(async () => {
    try {
        const response = await axios.get('/api/v1/products', {
            params: {
                is_featured: true,
                limit: 8,
                sort: 'featured'
            }
        });

        if (response.data.success) {
            featuredProducts.value = response.data.data.data || response.data.data;
        }
    } catch (error) {
        console.error('Error al cargar productos destacados:', error);
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <Head>
        <title>Inicio - PawfectShop</title>
        <meta name="description" content="Todo para tu mascota. Alimentos, juguetes, accesorios y más." />
    </Head>

    <!-- Hero Section -->
    <section class="relative w-full h-[85vh] overflow-hidden">
        <!-- Background Image with Overlay -->
        <div class="absolute inset-0 bg-gradient-to-r from-slate-900/90 to-slate-900/40 z-10"></div>
        <img
            src="https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=1920&q=80"
            alt="Perro feliz"
            class="absolute inset-0 w-full h-full object-cover"
        />

        <!-- Hero Content -->
        <div class="relative z-20 max-w-7xl mx-auto px-6 lg:px-20 h-full flex items-center">
            <div class="max-w-2xl space-y-8">
                <h1 class="text-5xl lg:text-7xl font-extrabold text-white leading-tight">
                    Todo para tu<br/>
                    <span class="text-primary">mejor amigo</span>
                </h1>
                <p class="text-xl text-slate-300 leading-relaxed">
                    Descubre nuestra selección premium de alimentos, juguetes y accesorios para perros, gatos y pequeñas mascotas.
                </p>
                <div class="flex flex-wrap gap-4">
                    <Link
                        :href="route('catalog.index')"
                        class="px-8 py-4 bg-primary text-background-dark font-bold text-lg rounded-2xl hover:scale-105 active:scale-95 transition-all shadow-xl shadow-primary/30 inline-flex items-center gap-2"
                    >
                        Comprar Ahora
                        <span class="material-symbols-outlined">arrow_forward</span>
                    </Link>
                    <Link
                        :href="route('catalog.index')"
                        class="px-8 py-4 bg-white/10 backdrop-blur-sm text-white font-bold text-lg rounded-2xl hover:bg-white/20 transition-all inline-flex items-center gap-2"
                    >
                        Ver Catálogo
                        <span class="material-symbols-outlined">pets</span>
                    </Link>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section class="py-20 px-6 lg:px-20 bg-white dark:bg-slate-900">
        <div class="max-w-7xl mx-auto">
            <!-- Section Header -->
            <div class="text-center mb-16">
                <h2 class="text-4xl font-extrabold mb-4">
                    Productos <span class="text-primary">Destacados</span>
                </h2>
                <p class="text-slate-600 dark:text-slate-400 text-lg max-w-2xl mx-auto">
                    Nuestra selección de los mejores productos para tu mascota, elegidos por expertos en cuidado animal.
                </p>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <div v-for="i in 4" :key="i" class="animate-pulse">
                    <div class="bg-slate-200 dark:bg-slate-800 rounded-2xl aspect-square mb-4"></div>
                    <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded mb-2"></div>
                    <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded w-2/3"></div>
                </div>
            </div>

            <!-- Products Grid -->
            <div v-else-if="featuredProducts.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <ProductCard
                    v-for="product in featuredProducts"
                    :key="product.id"
                    :product="product"
                />
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
                <span class="material-symbols-outlined text-6xl text-slate-300">pets</span>
                <p class="text-slate-500 mt-4">No hay productos destacados disponibles</p>
            </div>

            <!-- View All Products CTA -->
            <div class="text-center mt-16">
                <Link
                    :href="route('catalog.index')"
                    class="inline-flex items-center gap-2 px-8 py-4 border-2 border-primary text-primary font-bold rounded-2xl hover:bg-primary hover:text-background-dark transition-all"
                >
                    Ver Todos los Productos
                    <span class="material-symbols-outlined">arrow_forward</span>
                </Link>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="py-20 px-6 lg:px-20">
        <div class="max-w-7xl mx-auto">
            <div class="bg-primary/10 rounded-[2rem] p-12 lg:p-16 border border-primary/20">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div class="space-y-6">
                        <h2 class="text-4xl font-extrabold">
                            Únete a Nuestra <span class="text-primary">Comunidad</span>
                        </h2>
                        <p class="text-lg text-slate-600 dark:text-slate-400">
                            Suscríbete para recibir consejos de cuidado animal, ofertas exclusivas y novedades sobre nuestros productos.
                        </p>
                        <div class="flex items-center gap-4 pt-4">
                            <div class="flex -space-x-3">
                                <div class="w-12 h-12 rounded-full bg-primary border-4 border-white dark:border-slate-900 flex items-center justify-center text-white font-bold">
                                    <span class="material-symbols-outlined text-sm">person</span>
                                </div>
                                <div class="w-12 h-12 rounded-full bg-green-500 border-4 border-white dark:border-slate-900 flex items-center justify-center text-white font-bold">
                                    <span class="material-symbols-outlined text-sm">person</span>
                                </div>
                                <div class="w-12 h-12 rounded-full bg-blue-500 border-4 border-white dark:border-slate-900 flex items-center justify-center text-white font-bold">
                                    <span class="material-symbols-outlined text-sm">person</span>
                                </div>
                            </div>
                            <div class="text-sm">
                                <p class="font-bold">+5,000 miembros</p>
                                <p class="text-slate-500">Ya forman parte</p>
                            </div>
                        </div>
                    </div>

                    <!-- Newsletter Form -->
                    <form @submit.prevent="console.log('Subscribe')" class="space-y-4">
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                                mail
                            </span>
                            <input
                                type="email"
                                placeholder="Tu correo electrónico"
                                class="w-full pl-12 pr-4 py-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary/50 focus:border-primary"
                                required
                            />
                        </div>
                        <button
                            type="submit"
                            class="w-full bg-primary text-background-dark font-bold py-4 rounded-xl hover:scale-[1.02] active:scale-95 transition-all shadow-lg shadow-primary/20 flex items-center justify-center gap-2"
                        >
                            Suscribirse Ahora
                            <span class="material-symbols-outlined">send</span>
                        </button>
                        <p class="text-xs text-slate-500 text-center">
            Al suscribirte, aceptas nuestra política de privacidad. Puedes darte de baja en cualquier momento.
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Preview -->
    <section class="py-20 px-6 lg:px-20 bg-white dark:bg-slate-900">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-extrabold mb-4">
                    Categorías <span class="text-primary">Populares</span>
                </h2>
                <p class="text-slate-600 dark:text-slate-400 text-lg">
                    Encuentra exactamente lo que necesitas para tu mascota
                </p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <Link
                    :href="route('catalog.index', { category: 'perros' })"
                    class="group relative aspect-square rounded-2xl overflow-hidden"
                >
                    <img
                        src="https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=400&q=80"
                        alt="Perros"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/20 to-transparent"></div>
                    <div class="absolute bottom-6 left-6 right-6">
                        <h3 class="text-2xl font-bold text-white">Perros</h3>
                        <p class="text-sm text-slate-300">+50 productos</p>
                    </div>
                </Link>

                <Link
                    :href="route('catalog.index', { category: 'gatos' })"
                    class="group relative aspect-square rounded-2xl overflow-hidden"
                >
                    <img
                        src="https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=400&q=80"
                        alt="Gatos"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/20 to-transparent"></div>
                    <div class="absolute bottom-6 left-6 right-6">
                        <h3 class="text-2xl font-bold text-white">Gatos</h3>
                        <p class="text-sm text-slate-300">+40 productos</p>
                    </div>
                </Link>

                <Link
                    :href="route('catalog.index', { category: 'juguetes' })"
                    class="group relative aspect-square rounded-2xl overflow-hidden"
                >
                    <img
                        src="https://images.unsplash.com/photo-1596492784531-6e6eb5ea4205?w=400&q=80"
                        alt="Juguetes"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/20 to-transparent"></div>
                    <div class="absolute bottom-6 left-6 right-6">
                        <h3 class="text-2xl font-bold text-white">Juguetes</h3>
                        <p class="text-sm text-slate-300">+30 productos</p>
                    </div>
                </Link>

                <Link
                    :href="route('catalog.index', { category: 'accesorios' })"
                    class="group relative aspect-square rounded-2xl overflow-hidden"
                >
                    <img
                        src="https://images.unsplash.com/photo-1601758228041-f3b2795255f1?w=400&q=80"
                        alt="Accesorios"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/20 to-transparent"></div>
                    <div class="absolute bottom-6 left-6 right-6">
                        <h3 class="text-2xl font-bold text-white">Accesorios</h3>
                        <p class="text-sm text-slate-300">+25 productos</p>
                    </div>
                </Link>
            </div>
        </div>
    </section>
</template>
