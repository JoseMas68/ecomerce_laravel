<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useCart } from '@/Composables/useCart';

const props = defineProps({
    product: {
        type: Object,
        required: true
    },
    showAddToCart: {
        type: Boolean,
        default: true
    },
    showWishlist: {
        type: Boolean,
        default: true
    }
});

const emit = defineEmits(['addToCart', 'toggleWishlist']);

const { addItem } = useCart();

const isWishlisted = computed(() => props.product.is_wishlisted || false);

const handleAddToCart = async () => {
    const result = await addItem(props.product.id, 1);
    if (result.success) {
        emit('addToCart', props.product);
    }
};

const toggleWishlist = () => {
    emit('toggleWishlist', props.product);
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: 'EUR'
    }).format(price);
};

const discountPercentage = computed(() => {
    if (props.product.compare_at_price && props.product.compare_at_price > props.product.price) {
        const discount = ((props.product.compare_at_price - props.product.price) / props.product.compare_at_price) * 100;
        return Math.round(discount);
    }
    return 0;
});
</script>

<template>
    <div class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 overflow-hidden hover:shadow-lg transition-all duration-300">
        <!-- Imagen del Producto -->
        <div class="relative aspect-square overflow-hidden bg-slate-100 dark:bg-slate-800">
            <Link :href="`/catalog/${product.slug}`">
                <img
                    :src="product.image_url || '/images/placeholder-product.svg'"
                    :alt="product.name"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                />
            </Link>

            <!-- Badge de Descuento -->
            <div v-if="discountPercentage > 0"
                 class="absolute top-3 left-3 bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-full">
                -{{ discountPercentage }}%
            </div>

            <!-- Botón Wishlist -->
            <button
                v-if="showWishlist"
                @click="toggleWishlist"
                class="absolute top-3 right-3 w-8 h-8 bg-white dark:bg-slate-900 rounded-full flex items-center justify-center shadow-md hover:scale-110 transition-transform"
                :class="{ 'text-red-500': isWishlisted, 'text-slate-400 hover:text-red-500': !isWishlisted }"
            >
                <span class="material-symbols-outlined text-sm">
                    {{ isWishlisted ? 'favorite' : 'favorite_border' }}
                </span>
            </button>

            <!-- Badge de Stock -->
            <div v-if="product.stock_status !== 'in_stock'"
                 class="absolute bottom-3 left-3 right-3">
                <span class="block text-center bg-slate-900/80 text-white text-xs font-semibold py-1.5 rounded-lg backdrop-blur-sm">
                    {{ product.stock_status === 'out_of_stock' ? 'Agotado' : 'Bajo Stock' }}
                </span>
            </div>
        </div>

        <!-- Info del Producto -->
        <div class="p-4 space-y-2">
            <!-- Categoría -->
            <div v-if="product.category" class="text-xs text-slate-500 font-medium">
                {{ product.category.name }}
            </div>

            <!-- Nombre -->
            <Link :href="`/catalog/${product.slug}`">
                <h3 class="font-bold text-slate-900 dark:text-slate-100 group-hover:text-primary transition-colors line-clamp-2">
                    {{ product.name }}
                </h3>
            </Link>

            <!-- Rating -->
            <div v-if="product.rating" class="flex items-center gap-1">
                <div class="flex text-primary">
                    <span v-for="i in 5" :key="i" class="material-symbols-outlined text-sm">
                        {{ i <= Math.round(product.rating) ? 'star' : 'star_border' }}
                    </span>
                </div>
                <span class="text-xs text-slate-500">({{ product.reviews_count || 0 }})</span>
            </div>

            <!-- Precio -->
            <div class="flex items-center gap-2">
                <span class="text-lg font-bold text-primary">
                    {{ formatPrice(product.price) }}
                </span>
                <span v-if="product.compare_at_price && product.compare_at_price > product.price"
                      class="text-sm text-slate-400 line-through">
                    {{ formatPrice(product.compare_at_price) }}
                </span>
            </div>

            <!-- Botón Add to Cart -->
            <button
                v-if="showAddToCart && product.stock_status === 'in_stock'"
                @click="handleAddToCart"
                class="w-full mt-2 bg-primary text-background-dark font-semibold py-2.5 rounded-xl hover:bg-opacity-90 transition-opacity flex items-center justify-center gap-2"
            >
                <span class="material-symbols-outlined text-sm">add_shopping_cart</span>
                Añadir al Carrito
            </button>
        </div>
    </div>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
