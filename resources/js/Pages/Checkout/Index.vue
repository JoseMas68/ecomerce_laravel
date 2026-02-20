<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { useCart } from '@/Composables/useCart';
import { route } from '@/Composables/useRoutes';

defineOptions({ layout: AuthenticatedLayout });

const { cartItems, cartTotal, loading: cartLoading, getCart } = useCart();

const loading = ref(false);
const step = ref(1); // 1: Shipping, 2: Payment, 3: Success

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    address: '',
    city: '',
    postal_code: '',
    country: 'España',
    payment_method: 'card',
    card_name: '',
    card_number: '',
    card_expiry: '',
    card_cvc: ''
});

const subtotal = computed(() => {
    return cartItems.value.reduce((total, item) => total + (item.unit_price * item.quantity), 0);
});

const shipping = computed(() => subtotal.value > 50 ? 0 : 5.99);

const formatPrice = (price) => {
    return new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: 'EUR'
    }).format(price || 0);
};

const goToPayment = () => {
    if (form.first_name && form.last_name && form.address && form.city && form.postal_code) {
        step.value = 2;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
        alert('Por favor, completa todos los campos de envío requeridos.');
    }
};

const submitOrder = async () => {
    loading.value = true;
    
    // Simulate API delay for placing order
    setTimeout(() => {
        loading.value = false;
        step.value = 3;
        window.scrollTo({ top: 0, behavior: 'smooth' });
        // En una app real aqui limpiariamos el carrito y mandariamos al servidor
    }, 2000);
};

onMounted(async () => {
    await getCart();
    
    // Si no hay items, redirigir al carrito
    if (!cartLoading.value && cartItems.value.length === 0) {
        router.visit(route('cart.index'));
    }
});
</script>

<template>
    <Head>
        <title>Finalizar Compra - PawfectShop</title>
    </Head>

    <div class="min-h-screen bg-slate-50 dark:bg-slate-900 py-8 lg:py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            
            <div class="mb-8">
                <h1 class="text-3xl lg:text-4xl font-extrabold text-slate-900 dark:text-white mb-2">
                    Finalizar Compra
                </h1>
                <p class="text-slate-600 dark:text-slate-400">Completa tu pedido de forma segura.</p>
            </div>

            <div v-if="step === 3" class="bg-white dark:bg-slate-800 rounded-3xl shadow-xl p-8 lg:p-16 text-center max-w-3xl mx-auto">
                <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="material-symbols-outlined text-green-500 text-5xl">check_circle</span>
                </div>
                <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white mb-4">¡Pedido Confirmado!</h2>
                <p class="text-slate-600 dark:text-slate-300 mb-8 text-lg">
                    Gracias por tu compra. Te hemos enviado un email con la confirmación de tu pedido #ORD-{{ Math.floor(Math.random() * 100000) }}.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link :href="route('profile.orders')" class="px-8 py-4 bg-slate-100 dark:bg-slate-700 text-slate-900 dark:text-white font-bold rounded-2xl hover:bg-slate-200 transition-colors">
                        Ver mis pedidos
                    </Link>
                    <Link :href="route('catalog.index')" class="px-8 py-4 bg-primary text-background-dark font-extrabold rounded-2xl shadow-lg shadow-primary/30 hover:scale-[1.02] transition-transform">
                        Seguir Comprando
                    </Link>
                </div>
            </div>

            <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column: Forms -->
                <div class="lg:col-span-2 space-y-8">
                    
                    <!-- Progress Tracker -->
                    <div class="flex items-center justify-between mb-8 relative">
                        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1 bg-slate-200 dark:bg-slate-700 rounded-full -z-10"></div>
                        <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-primary rounded-full -z-10 transition-all duration-500" :style="{ width: step === 2 ? '100%' : '0%' }"></div>
                        
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold transition-colors" 
                                 :class="step >= 1 ? 'bg-primary text-background-dark' : 'bg-slate-200 text-slate-500'">
                                1
                            </div>
                            <span class="text-sm font-bold" :class="step >= 1 ? 'text-primary' : 'text-slate-500'">Envío</span>
                        </div>
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold transition-colors"
                                 :class="step >= 2 ? 'bg-primary text-background-dark' : 'bg-slate-200 dark:bg-slate-700 text-slate-500'">
                                2
                            </div>
                            <span class="text-sm font-bold" :class="step >= 2 ? 'text-primary' : 'text-slate-500'">Pago</span>
                        </div>
                    </div>

                    <!-- Step 1: Shipping Form -->
                    <div v-show="step === 1" class="bg-white dark:bg-slate-800 rounded-3xl shadow-xl p-6 lg:p-8">
                        <h2 class="text-2xl font-bold flex items-center gap-2 mb-6 text-slate-900 dark:text-white">
                            <span class="material-symbols-outlined text-primary">local_shipping</span>
                            Dirección de Envío
                        </h2>
                        
                        <form @submit.prevent="goToPayment" class="space-y-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Nombre *</label>
                                    <input v-model="form.first_name" type="text" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Apellidos *</label>
                                    <input v-model="form.last_name" type="text" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Email *</label>
                                    <input v-model="form.email" type="email" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Dirección Completa *</label>
                                    <input v-model="form.address" type="text" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none" placeholder="Calle, número, piso, puerta...">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Ciudad *</label>
                                    <input v-model="form.city" type="text" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Código Postal *</label>
                                    <input v-model="form.postal_code" type="text" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Teléfono</label>
                                    <input v-model="form.phone" type="tel" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none">
                                </div>
                            </div>
                            
                            <div class="pt-6">
                                <button type="submit" class="w-full bg-primary text-background-dark font-extrabold rounded-2xl py-4 flex items-center justify-center gap-2 hover:bg-[#5cdb42] transition-colors shadow-lg shadow-primary/30">
                                    Continuar al Pago
                                    <span class="material-symbols-outlined">arrow_forward</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Step 2: Payment Form -->
                    <div v-show="step === 2" class="bg-white dark:bg-slate-800 rounded-3xl shadow-xl p-6 lg:p-8">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold flex items-center gap-2 text-slate-900 dark:text-white">
                                <span class="material-symbols-outlined text-primary">credit_card</span>
                                Método de Pago
                            </h2>
                            <button @click="step = 1" class="text-sm font-semibold text-slate-500 hover:text-primary transition-colors flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">edit</span> Editar envío
                            </button>
                        </div>
                        
                        <form @submit.prevent="submitOrder" class="space-y-6">
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <label class="border-2 border-primary bg-primary/5 rounded-2xl p-4 cursor-pointer flex items-center gap-3">
                                    <input type="radio" v-model="form.payment_method" value="card" class="text-primary focus:ring-primary">
                                    <span class="font-bold flex-1 text-slate-900 dark:text-white">Tarjeta de Crédito</span>
                                    <span class="material-symbols-outlined text-slate-400">credit_card</span>
                                </label>
                                <label class="border-2 border-slate-200 dark:border-slate-700 rounded-2xl p-4 cursor-pointer flex items-center gap-3 opacity-50">
                                    <input type="radio" disabled class="text-primary focus:ring-primary">
                                    <span class="font-bold flex-1 text-slate-900 dark:text-white">PayPal <span class="text-xs font-normal text-slate-500 ml-1">(No disp.)</span></span>
                                    <svg class="h-6 w-6 text-[#003087]" viewBox="0 0 24 24" fill="currentColor"><path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81 1.01 1.15 1.304 2.42 1.012 4.287-.023.143-.047.288-.077.437-.983 5.05-4.349 6.797-8.647 6.797h-2.19c-.524 0-.968.382-1.05.9l-1.12 7.106z"/></svg>
                                </label>
                            </div>

                            <div v-if="form.payment_method === 'card'" class="space-y-6 bg-slate-50 dark:bg-slate-900/50 p-6 rounded-2xl border border-slate-100 dark:border-slate-800">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Titular de la tarjeta *</label>
                                    <input v-model="form.card_name" type="text" required placeholder="Nombre como aparece en la tarjeta" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none">
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Número de tarjeta *</label>
                                    <div class="relative">
                                        <input v-model="form.card_number" type="text" required placeholder="0000 0000 0000 0000" maxlength="19" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl pl-12 pr-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none">
                                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">credit_card</span>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Caducidad *</label>
                                        <input v-model="form.card_expiry" type="text" required placeholder="MM/YY" maxlength="5" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none text-center">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">CVC *</label>
                                        <div class="relative">
                                            <input v-model="form.card_cvc" type="text" required placeholder="123" maxlength="4" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl pl-4 pr-10 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none text-center">
                                            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm cursor-help" title="Códgio de 3 o 4 dígitos detrás de su tarjeta">help</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-6">
                                <button type="submit" :disabled="loading" class="w-full bg-slate-900 dark:bg-primary text-white dark:text-background-dark font-extrabold rounded-2xl py-4 flex items-center justify-center gap-2 hover:-translate-y-1 transition-transform shadow-lg shadow-primary/20 disabled:opacity-70 disabled:hover:translate-y-0">
                                    <span v-if="loading" class="material-symbols-outlined animate-spin">sync</span>
                                    <span v-else class="material-symbols-outlined">lock</span>
                                    {{ loading ? 'Procesando pago...' : `Pagar ${formatPrice(cartTotal)} Seguramente` }}
                                </button>
                                <p class="text-xs text-center text-slate-500 mt-4 flex items-center justify-center gap-1">
                                    <span class="material-symbols-outlined text-sm text-green-500">verified_user</span> Pagos encriptados con seguridad SSL de 256 bits
                                </p>
                            </div>
                        </form>
                    </div>

                </div>

                <!-- Right Column: Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-xl p-6 lg:p-8 sticky top-24">
                        <h2 class="text-xl font-bold mb-6 text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-700 pb-4">
                            Resumen de Pedido
                        </h2>

                        <!-- Items List -->
                        <div class="space-y-4 mb-6 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                            <div v-if="cartLoading" class="animate-pulse flex space-x-4">
                                <div class="rounded-xl bg-slate-200 h-16 w-16"></div>
                                <div class="flex-1 space-y-3 py-1">
                                    <div class="h-2 bg-slate-200 rounded"></div>
                                    <div class="space-y-2">
                                        <div class="h-2 bg-slate-200 rounded w-4/6"></div>
                                    </div>
                                </div>
                            </div>

                            <div v-for="item in cartItems" :key="item.id" class="flex gap-4">
                                <div class="w-16 h-16 bg-slate-100 dark:bg-slate-700 rounded-xl overflow-hidden flex-shrink-0 relative">
                                    <img :src="item.product.image_url" :alt="item.product.name" class="w-full h-full object-cover">
                                    <span class="absolute -top-2 -right-2 bg-slate-500 text-white w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold border-2 border-white dark:border-slate-800">
                                        {{ item.quantity }}
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ item.product.name }}</h4>
                                    <p class="text-primary font-bold">{{ formatPrice(item.unit_price) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Totals -->
                        <div class="space-y-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                            <div class="flex justify-between text-slate-600 dark:text-slate-400">
                                <span>Subtotal</span>
                                <span class="font-medium text-slate-900 dark:text-white">{{ formatPrice(subtotal) }}</span>
                            </div>
                            <div class="flex justify-between text-slate-600 dark:text-slate-400">
                                <span>Envío</span>
                                <span v-if="shipping === 0" class="font-bold text-green-500 tracking-wide uppercase text-sm">Gratis</span>
                                <span v-else class="font-medium text-slate-900 dark:text-white">{{ formatPrice(shipping) }}</span>
                            </div>
                            <div class="flex justify-between text-slate-600 dark:text-slate-400">
                                <span>Impuestos (IVA 21%)</span>
                                <span class="font-medium text-slate-900 dark:text-white">{{ formatPrice(subtotal * 0.21) }}</span>
                            </div>
                        </div>

                        <div class="mt-6 pt-6 border-t-2 border-dashed border-slate-200 dark:border-slate-700">
                            <div class="flex justify-between items-end">
                                <span class="text-lg font-bold text-slate-800 dark:text-white">Total</span>
                                <div class="text-right">
                                    <span class="text-3xl font-extrabold text-primary">{{ formatPrice(cartTotal + shipping) }}</span>
                                    <p class="text-xs text-slate-500 mt-1">EUR inc. impuestos</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #475569;
}
</style>
