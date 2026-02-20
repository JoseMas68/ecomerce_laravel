import { ref, computed } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';

/**
 * Composable para gestionar el carrito de compras
 * Proporciona estado reactivo y métodos para interactuar con la API del carrito
 */
export function useCart() {
    // ==================== ESTADO REACTIVO ====================

    /**
     * Carrito completo con todos sus datos
     */
    const cart = ref(null);

    /**
     * Array de items en el carrito
     */
    const items = ref([]);

    /**
     * Total del carrito
     */
    const total = ref(0);

    /**
     * Cantidad total de items en el carrito
     */
    const itemsCount = ref(0);

    /**
     * Estado de carga para operaciones asíncronas
     */
    const loading = ref(false);

    /**
     * Mensaje de error de la última operación
     */
    const error = ref(null);

    // ==================== COMPUTED PROPERTIES ====================

    /**
     * Subtotal calculado de los items del carrito
     */
    const subtotal = computed(() => {
        return items.value.reduce((sum, item) => {
            return sum + (item.price * item.quantity);
        }, 0);
    });

    /**
     * Verifica si el carrito está vacío
     */
    const isEmpty = computed(() => {
        return items.value.length === 0;
    });

    // ==================== MÉTODOS ====================

    /**
     * Obtiene el carrito desde la API
     * Actualiza todo el estado del carrito con los datos del servidor
     *
     * @returns {Promise<boolean>} True si se obtuvo correctamente
     */
    const fetchCart = async () => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.get('/api/v1/cart');

            if (response.data.success) {
                const cartData = response.data.data;

                // Actualizar estado del carrito
                cart.value = cartData;
                items.value = cartData.items || [];
                total.value = cartData.summary?.total || 0;
                itemsCount.value = items.value.reduce((sum, item) => sum + item.quantity, 0);

                return true;
            }

            error.value = response.data.message || 'Error al obtener el carrito';
            return false;
        } catch (err) {
            console.error('Error al obtener carrito:', err);

            // Manejar error 401 - No autenticado
            if (err.response?.status === 401) {
                router.visit('/auth/login', {
                    method: 'get',
                    preserveState: false
                });
                return false;
            }

            error.value = err.response?.data?.message || 'Error al conectar con el servidor';
            return false;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Agrega un producto al carrito
     *
     * @param {number|string} productId - ID del producto a agregar
     * @param {number} quantity - Cantidad a agregar (default: 1)
     * @param {object|null} variant - Variante del producto si aplica (default: null)
     * @returns {Promise<{success: boolean, message: string}>}
     */
    const addItem = async (productId, quantity = 1, variant = null) => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.post('/api/v1/cart/items', {
                product_id: productId,
                quantity,
                variant
            });

            if (response.data.success) {
                // Actualizar el carrito después de agregar
                await fetchCart();
                return {
                    success: true,
                    message: response.data.message || 'Producto agregado al carrito'
                };
            }

            error.value = response.data.message || 'Error al agregar al carrito';
            return { success: false, message: error.value };
        } catch (err) {
            console.error('Error al agregar item:', err);

            // Manejar error 401
            if (err.response?.status === 401) {
                router.visit('/auth/login', {
                    method: 'get',
                    preserveState: false
                });
                return { success: false, message: 'Debes iniciar sesión' };
            }

            error.value = err.response?.data?.message || 'Error al conectar con el servidor';
            return { success: false, message: error.value };
        } finally {
            loading.value = false;
        }
    };

    /**
     * Actualiza la cantidad de un item en el carrito
     * Si la cantidad es 0, elimina el item del carrito
     *
     * @param {number|string} itemId - ID del item a actualizar
     * @param {number} quantity - Nueva cantidad
     * @returns {Promise<{success: boolean, message: string}>}
     */
    const updateQuantity = async (itemId, quantity) => {
        // Si la cantidad es 0, eliminar el item
        if (quantity < 1) {
            return removeItem(itemId);
        }

        loading.value = true;
        error.value = null;

        try {
            const response = await axios.put(`/api/v1/cart/items/${itemId}`, {
                quantity
            });

            if (response.data.success) {
                // Actualizar localmente para mejor UX
                const itemIndex = items.value.findIndex(item => item.id === itemId);
                if (itemIndex !== -1) {
                    items.value[itemIndex].quantity = quantity;
                    // Recalcular itemsCount
                    itemsCount.value = items.value.reduce((sum, item) => sum + item.quantity, 0);
                }

                // Recargar para sincronizar con el servidor
                await fetchCart();
                return { success: true };
            }

            error.value = response.data.message || 'Error al actualizar cantidad';
            return { success: false, message: error.value };
        } catch (err) {
            console.error('Error al actualizar cantidad:', err);

            // Manejar error 401
            if (err.response?.status === 401) {
                router.visit('/auth/login', {
                    method: 'get',
                    preserveState: false
                });
                return { success: false, message: 'Debes iniciar sesión' };
            }

            error.value = err.response?.data?.message || 'Error al conectar con el servidor';
            return { success: false, message: error.value };
        } finally {
            loading.value = false;
        }
    };

    /**
     * Elimina un item del carrito
     *
     * @param {number|string} itemId - ID del item a eliminar
     * @returns {Promise<{success: boolean, message: string}>}
     */
    const removeItem = async (itemId) => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.delete(`/api/v1/cart/items/${itemId}`);

            if (response.data.success) {
                // Eliminar localmente para mejor UX
                items.value = items.value.filter(item => item.id !== itemId);
                // Recalcular itemsCount
                itemsCount.value = items.value.reduce((sum, item) => sum + item.quantity, 0);

                // Recargar para sincronizar con el servidor
                await fetchCart();
                return { success: true };
            }

            error.value = response.data.message || 'Error al eliminar item';
            return { success: false, message: error.value };
        } catch (err) {
            console.error('Error al eliminar item:', err);

            // Manejar error 401
            if (err.response?.status === 401) {
                router.visit('/auth/login', {
                    method: 'get',
                    preserveState: false
                });
                return { success: false, message: 'Debes iniciar sesión' };
            }

            error.value = err.response?.data?.message || 'Error al conectar con el servidor';
            return { success: false, message: error.value };
        } finally {
            loading.value = false;
        }
    };

    /**
     * Limpia todo el carrito
     * Elimina todos los items y resetea el estado
     *
     * @returns {Promise<{success: boolean, message: string}>}
     */
    const clearCart = async () => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.delete('/api/v1/cart');

            if (response.data.success) {
                // Limpiar estado local
                cart.value = null;
                items.value = [];
                total.value = 0;
                itemsCount.value = 0;
                error.value = null;

                return { success: true };
            }

            error.value = response.data.message || 'Error al limpiar carrito';
            return { success: false, message: error.value };
        } catch (err) {
            console.error('Error al limpiar carrito:', err);

            // Manejar error 401
            if (err.response?.status === 401) {
                router.visit('/auth/login', {
                    method: 'get',
                    preserveState: false
                });
                return { success: false, message: 'Debes iniciar sesión' };
            }

            error.value = err.response?.data?.message || 'Error al conectar con el servidor';
            return { success: false, message: error.value };
        } finally {
            loading.value = false;
        }
    };

    /**
     * Recarga el carrito desde el servidor
     * Alias de fetchCart() - útil para recargar después de login/logout
     *
     * @returns {Promise<boolean>} True si se recargó correctamente
     */
    const refreshCart = async () => {
        return fetchCart();
    };

    /**
     * Aplica un cupón de descuento al carrito
     *
     * @param {string} couponCode - Código del cupón
     * @returns {Promise<{success: boolean, message: string}>}
     */
    const applyCoupon = async (couponCode) => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.post('/api/v1/cart/coupon', {
                code: couponCode
            });

            if (response.data.success) {
                await fetchCart();
                return {
                    success: true,
                    message: response.data.message || 'Cupón aplicado correctamente'
                };
            }

            error.value = response.data.message || 'Error al aplicar cupón';
            return { success: false, message: error.value };
        } catch (err) {
            console.error('Error al aplicar cupón:', err);

            // Manejar error 401
            if (err.response?.status === 401) {
                router.visit('/auth/login', {
                    method: 'get',
                    preserveState: false
                });
                return { success: false, message: 'Debes iniciar sesión' };
            }

            error.value = err.response?.data?.message || 'Cupón inválido o expirado';
            return { success: false, message: error.value };
        } finally {
            loading.value = false;
        }
    };

    /**
     * Remueve el cupón de descuento del carrito
     *
     * @returns {Promise<{success: boolean, message: string}>}
     */
    const removeCoupon = async () => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.delete('/api/v1/cart/coupon');

            if (response.data.success) {
                await fetchCart();
                return { success: true };
            }

            error.value = response.data.message || 'Error al remover cupón';
            return { success: false, message: error.value };
        } catch (err) {
            console.error('Error al remover cupón:', err);

            // Manejar error 401
            if (err.response?.status === 401) {
                router.visit('/auth/login', {
                    method: 'get',
                    preserveState: false
                });
                return { success: false, message: 'Debes iniciar sesión' };
            }

            error.value = err.response?.data?.message || 'Error al conectar con el servidor';
            return { success: false, message: error.value };
        } finally {
            loading.value = false;
        }
    };

    /**
     * Obtiene el resumen del carrito (subtotal, envío, impuestos, total)
     *
     * @returns {Promise<object|null>} Resumen del carrito o null si hay error
     */
    const fetchSummary = async () => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.get('/api/v1/cart/summary');

            if (response.data.success) {
                const summary = response.data.data;
                total.value = summary.total || 0;
                return summary;
            }

            error.value = response.data.message || 'Error al obtener resumen';
            return null;
        } catch (err) {
            console.error('Error al obtener resumen:', err);

            // Manejar error 401
            if (err.response?.status === 401) {
                router.visit('/auth/login', {
                    method: 'get',
                    preserveState: false
                });
                return null;
            }

            error.value = err.response?.data?.message || 'Error al conectar con el servidor';
            return null;
        } finally {
            loading.value = false;
        }
    };

    // ==================== RETORNO ====================

    return {
        // Estado reactivo
        cart,
        items,
        total,
        itemsCount,
        loading,
        error,

        // Propiedades computadas
        subtotal,
        isEmpty,

        // Métodos principales
        fetchCart,
        addItem,
        updateQuantity,
        removeItem,
        clearCart,
        refreshCart,

        // Métodos adicionales
        applyCoupon,
        removeCoupon,
        fetchSummary
    };
}
