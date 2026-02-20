<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { Link, Head, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { useCart } from '@/Composables/useCart';
import { useAuth } from '@/Composables/useAuth';

defineOptions({ layout: AuthenticatedLayout });

const { cart, items, total, itemsCount, loading, fetchCart, updateQuantity, removeItem, clearCart, applyCoupon, removeCoupon } = useCart();
const { isAuthenticated } = useAuth();

const couponCode = ref('');
const couponLoading = ref(false);
const updating = ref(false);

// Computed properties para el resumen
const summary = computed(() => {
    const subtotal = cart.value?.items?.reduce((sum, item) => sum + (item.subtotal || 0), 0) || 0;
    const shipping = subtotal >= 50 ? 0 : 4.99; // Envío gratis para pedidos >= 50€
    const tax = subtotal * 0.21; // IVA 21%
    const discount = cart.value?.discount_amount || 0;
    const total = subtotal + shipping + tax - discount;

    return {
        subtotal,
        shipping,
        tax,
        discount,
        total,
        coupon: cart.value?.coupon_code ? { code: cart.value.coupon_code, discount: 10 } : null
    };
});

onMounted(async () => {
    if (isAuthenticated.value) {
        await fetchCart();
    }
});

const handleUpdateQuantity = async (itemId, quantity) => {
    updating.value = true;
    await updateQuantity(itemId, quantity);
    updating.value = false;
};

const handleRemoveItem = async (itemId) => {
    if (confirm('¿Estás seguro de que quieres eliminar este producto?')) {
        updating.value = true;
        await removeItem(itemId);
        updating.value = false;
    }
};

const handleApplyCoupon = async () => {
    if (!couponCode.value.trim()) return;

    couponLoading.value = true;
    const result = await applyCoupon(couponCode.value);
    couponLoading.value = false;

    if (result.success) {
        couponCode.value = '';
        alert('Cupón aplicado correctamente');
    } else {
        alert(result.message);
    }
};

const handleRemoveCoupon = async () => {
    updating.value = true;
    await removeCoupon();
    updating.value = false;
};

const handleCheckout = () => {
    if (!isAuthenticated.value) {
        router.visit(route('login'), {
            data: { redirect: route('checkout.index') }
        });
        return;
    }

    if (items.value.length === 0) {
        alert('Tu carrito está vacío');
        return;
    }

    router.visit(route('checkout.index'));
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: 'EUR'
    }).format(price);
};

const totalItems = computed(() => {
    return itemsCount.value || items.value.reduce((sum, item) => sum + item.quantity, 0);
});
</script>

<template>
    <Head>
        <title>Carrito - PawfectShop</title>
        <meta name="description" content="Revisa tu carrito de compras" />
    </Head>

    <div v-if="!isAuthenticated" class="max-w-7xl mx-auto px-6 lg:px-20 py-20 text-center">
        <span class="material-symbols-outlined text-6xl text-slate-300">shopping_cart</span>
        <h2 class="text-2xl font-bold mt-4 mb-2">Inicia Sesión para Ver Tu Carrito</h2>
        <p class="text-slate-500 mb-6">Necesitas iniciar sesión para gestionar tu carrito de compras</p>
        <div class="flex justify-center gap-4">
            <Link
                :href="route('login')"
                class="px-6 py-3 bg-primary text-background-dark font-bold rounded-lg hover:opacity-90 transition-opacity"
            >
                Iniciar Sesión
            </Link>
            <Link
                :href="route('catalog.index')"
                class="px-6 py-3 border border-slate-300 dark:border-slate-700 font-semibold rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
            >
                Continuar Comprando
            </Link>
        </div>
    </div>

    <div v-else class="max-w-7xl mx-auto px-6 lg:px-20 py-12">
        <!-- Breadcrumbs -->
        <div class="flex items-center gap-2 text-sm text-slate-500 mb-8">
            <Link :href="route('home')" class="hover:text-primary transition-colors">
                Inicio
            </Link>
            <span class="material-symbols-outlined text-xs">chevron_right</span>
            <span class="font-medium text-slate-900 dark:text-slate-100">Carrito</span>
        </div>

        <!-- Page Header -->
        <h1 class="text-4xl font-extrabold mb-10">
            Tu Carrito <span class="text-primary text-2xl ml-2 font-normal">({{ totalItems }} {{ totalItems === 1 ? 'artículo' : 'artículos' }})</span>
        </h1>

        <div v-if="items.length === 0" class="text-center py-20">
            <span class="material-symbols-outlined text-6xl text-slate-300">production_empty</span>
            <h2 class="text-2xl font-bold mt-4 mb-2">Tu carrito está vacío</h2>
            <p class="text-slate-500 mb-6">¡Añade algunos productos para empezar!</p>
            <Link
                :href="route('catalog.index')"
                class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-background-dark font-bold rounded-lg hover:opacity-90 transition-opacity"
            >
                Explorar Productos
                <span class="material-symbols-outlined">arrow_forward</span>
            </Link>
        </div>

        <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-12 items-start">
            <!-- Items List -->
            <div class="lg:col-span-2 space-y-8">
                <div class="overflow-x-auto">
                    <table class="w-full text-left min-w-[600px]">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-sm font-semibold text-slate-500">
                                <th class="pb-4 w-1/2">Producto</th>
                                <th class="pb-4">Precio</th>
                                <th class="pb-4">Cantidad</th>
                                <th class="pb-4 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="item in items" :key="item.id" class="group">
                                <td class="py-6">
                                    <div class="flex gap-4">
                                        <div class="w-24 h-24 bg-white dark:bg-slate-800 rounded-xl overflow-hidden flex-shrink-0">
                                            <img
                                                :src="item.product?.image_url || item.product?.image || '/images/placeholder-product.svg'"
                                                :alt="item.product?.name"
                                                class="w-full h-full object-cover"
                                            />
                                        </div>
                                        <div class="flex flex-col justify-center">
                                            <Link
                                                :href="route('catalog.show', { slug: item.product?.slug })"
                                                class="font-bold text-lg group-hover:text-primary transition-colors"
                                            >
                                                {{ item.product?.name }}
                                            </Link>
                                            <p class="text-sm text-slate-500">
                                                {{ item.variant || 'Variante estándar' }}
                                            </p>
                                            <button
                                                @click="handleRemoveItem(item.id)"
                                                :disabled="updating"
                                                class="mt-2 flex items-center gap-1 text-xs font-semibold text-red-500 hover:text-red-600 transition-colors uppercase tracking-wider disabled:opacity-50"
                                            >
                                                <span class="material-symbols-outlined text-sm">delete</span>
                                                Eliminar
                                            </button>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-6 font-medium">
                                    {{ formatPrice(item.unit_price) }}
                                </td>
                                <td class="py-6">
                                    <div class="flex items-center border border-slate-200 dark:border-slate-700 rounded-lg w-fit bg-white dark:bg-slate-900 overflow-hidden">
                                        <button
                                            @click="handleUpdateQuantity(item.id, item.quantity - 1)"
                                            :disabled="updating || item.quantity <= 1"
                                            class="px-3 py-1 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-500 disabled:opacity-50"
                                        >
                                            <span class="material-symbols-outlined text-sm">remove</span>
                                        </button>
                                        <span class="px-4 py-1 text-sm font-bold border-x border-slate-200 dark:border-slate-700">
                                            {{ item.quantity }}
                                        </span>
                                        <button
                                            @click="handleUpdateQuantity(item.id, item.quantity + 1)"
                                            :disabled="updating"
                                            class="px-3 py-1 hover:bg-slate-50 dark:hover:bg-slate-800 text-primary disabled:opacity-50"
                                        >
                                            <span class="material-symbols-outlined text-sm">add</span>
                                        </button>
                                    </div>
                                </td>
                                <td class="py-6 text-right font-bold text-lg">
                                    {{ formatPrice(item.subtotal) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Continue Shopping + Coupon -->
                <div class="pt-8 flex flex-col sm:flex-row justify-between items-center gap-6">
                    <Link
                        :href="route('catalog.index')"
                        class="flex items-center gap-2 text-primary font-bold hover:gap-3 transition-all"
                    >
                        <span class="material-symbols-outlined">arrow_back</span>
                        Continuar Comprando
                    </Link>

                    <div class="flex w-full sm:w-auto gap-2">
                        <input
                            v-model="couponCode"
                            type="text"
                            placeholder="Código de cupón"
                            class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-4 py-3 text-sm focus:ring-primary/50 focus:border-primary w-full sm:w-48"
                            :disabled="summary.coupon"
                        />
                        <button
                            v-if="!summary.coupon"
                            @click="handleApplyCoupon"
                            :disabled="couponLoading || !couponCode"
                            class="bg-slate-900 dark:bg-primary text-white dark:text-background-dark font-bold px-6 py-3 rounded-lg text-sm hover:opacity-90 transition-opacity disabled:opacity-50"
                        >
                            Aplicar
                        </button>
                        <button
                            v-else
                            @click="handleRemoveCoupon"
                            :disabled="updating"
                            class="bg-red-500 text-white font-bold px-6 py-3 rounded-lg text-sm hover:opacity-90 transition-opacity disabled:opacity-50"
                        >
                            Remover
                        </button>
                    </div>
                </div>

                <!-- Applied Coupon -->
                <div v-if="summary.coupon" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-green-600">verified</span>
                            <span class="font-semibold text-green-800 dark:text-green-400">
                                Cupón aplicado: {{ summary.coupon.code }}
                            </span>
                            <span class="text-sm text-green-600 dark:text-green-500">
                                ({{ summary.coupon.discount }}% descuento)
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cart Summary -->
            <aside class="sticky top-28">
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-8 shadow-sm">
                    <h2 class="text-xl font-extrabold mb-6">Resumen del Pedido</h2>

                    <div class="space-y-4 mb-8">
                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>Subtotal</span>
                            <span class="font-semibold text-slate-900 dark:text-slate-100">
                                {{ formatPrice(summary.subtotal) }}
                            </span>
                        </div>

                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>Envío</span>
                            <span class="font-semibold" :class="summary.shipping === 0 ? 'text-primary' : 'text-slate-900 dark:text-slate-100'">
                                {{ summary.shipping === 0 ? 'GRATIS' : formatPrice(summary.shipping) }}
                            </span>
                        </div>

                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>Impuestos (estimado)</span>
                            <span class="font-semibold text-slate-900 dark:text-slate-100">
                                {{ formatPrice(summary.tax) }}
                            </span>
                        </div>

                        <div v-if="summary.coupon" class="flex justify-between text-green-600">
                            <span>Descuento</span>
                            <span class="font-semibold">
                                -{{ formatPrice(summary.discount) }}
                            </span>
                        </div>

                        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-between items-end">
                            <span class="text-lg font-bold">Total</span>
                            <span class="text-3xl font-extrabold text-primary">
                                {{ formatPrice(summary.total) }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <button
                            @click="handleCheckout"
                            class="w-full bg-primary py-4 rounded-xl text-background-dark font-extrabold text-lg shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all flex items-center justify-center gap-2"
                        >
                            Finalizar Compra
                            <span class="material-symbols-outlined">arrow_forward</span>
                        </button>
                        <p class="text-center text-xs text-slate-400">
                            Pago seguro con encriptación SSL de 128-bit
                        </p>
                    </div>

                    <!-- Payment Methods -->
                    <div class="mt-8 grid grid-cols-3 gap-4 opacity-50 grayscale hover:grayscale-0 transition-all duration-500">
                        <div class="flex justify-center">
                            <img src="https://img.icons8.com/color/48/visa.png" alt="Visa" class="h-8" />
                        </div>
                        <div class="flex justify-center">
                            <img src="https://img.icons8.com/color/48/mastercard.png" alt="Mastercard" class="h-8" />
                        </div>
                        <div class="flex justify-center">
                            <img src="https://img.icons8.com/color/48/paypal.png" alt="PayPal" class="h-8" />
                        </div>
                    </div>
                </div>

                <!-- Free Shipping Banner -->
                <div v-if="summary.shipping === 0" class="mt-6 p-6 bg-primary/5 rounded-2xl border border-primary/10 flex items-start gap-4">
                    <span class="material-symbols-outlined text-primary">local_shipping</span>
                    <div>
                        <h4 class="font-bold text-sm">Envío Express Gratis</h4>
                        <p class="text-xs text-slate-500 mt-1">
                            ¡Pedido dentro de las próximas 2 horas para envío el mismo día!
                        </p>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</template>
