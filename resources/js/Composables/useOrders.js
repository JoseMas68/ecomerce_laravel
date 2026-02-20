/**
 * Orders Composable
 * Gestiona el estado y operaciones de pedidos del usuario
 */
import { ref, computed } from 'vue';
import axios from 'axios';
import router from '@inertiajs/vue3';

// Estado reactivo
const orders = ref([]);
const currentOrder = ref(null);
const stats = ref(null);
const loading = ref(false);
const error = ref(null);

export function useOrders() {
    /**
     * Obtiene los pedidos del usuario autenticado
     */
    const fetchOrders = async (params = {}) => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.get('/api/v1/orders', { params });
            orders.value = response.data.data || response.data;
            return response.data;
        } catch (err) {
            if (err.response?.status === 401) {
                router.visit(route('login'));
                return;
            }
            error.value = err.response?.data?.message || 'Error al cargar pedidos';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Obtiene un pedido específico por ID
     */
    const fetchOrder = async (orderId) => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.get(`/api/v1/orders/${orderId}`);
            currentOrder.value = response.data.data;
            return response.data.data;
        } catch (err) {
            if (err.response?.status === 401) {
                router.visit(route('login'));
                return;
            }
            if (err.response?.status === 404) {
                error.value = 'Pedido no encontrado';
            } else {
                error.value = err.response?.data?.message || 'Error al cargar pedido';
            }
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Crea un nuevo pedido desde el carrito
     */
    const createOrder = async (orderData) => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.post('/api/v1/orders', orderData);
            currentOrder.value = response.data.data;

            // Refrescar lista de pedidos
            await fetchOrders();

            return {
                success: true,
                data: response.data.data,
                message: response.data.message || 'Pedido creado exitosamente'
            };
        } catch (err) {
            if (err.response?.status === 401) {
                router.visit(route('login'));
                return { success: false, message: 'No autenticado' };
            }

            const errorMessage = err.response?.data?.message || 'Error al crear pedido';
            error.value = errorMessage;

            return {
                success: false,
                message: errorMessage,
                errors: err.response?.data?.errors
            };
        } finally {
            loading.value = false;
        }
    };

    /**
     * Cancela un pedido
     */
    const cancelOrder = async (orderId, reason = null) => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.patch(`/api/v1/orders/${orderId}/cancel`, {
                reason
            });

            // Actualizar pedido en la lista
            const index = orders.value.findIndex(o => o.id === orderId);
            if (index !== -1) {
                orders.value[index] = response.data.data;
            }

            // Actualizar pedido actual si es el mismo
            if (currentOrder.value?.id === orderId) {
                currentOrder.value = response.data.data;
            }

            return {
                success: true,
                data: response.data.data,
                message: response.data.message || 'Pedido cancelado exitosamente'
            };
        } catch (err) {
            if (err.response?.status === 401) {
                router.visit(route('login'));
                return { success: false, message: 'No autenticado' };
            }

            const errorMessage = err.response?.data?.message || 'Error al cancelar pedido';
            error.value = errorMessage;

            return {
                success: false,
                message: errorMessage
            };
        } finally {
            loading.value = false;
        }
    };

    /**
     * Obtiene estadísticas del usuario
     */
    const fetchStats = async () => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.get('/api/v1/orders/stats');
            stats.value = response.data;
            return response.data;
        } catch (err) {
            if (err.response?.status === 401) {
                router.visit(route('login'));
                return;
            }
            error.value = err.response?.data?.message || 'Error al cargar estadísticas';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Obtiene información de rastreo de un pedido
     */
    const trackOrder = async (orderId) => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.get(`/api/v1/orders/${orderId}/track`);
            return response.data;
        } catch (err) {
            if (err.response?.status === 401) {
                router.visit(route('login'));
                return;
            }
            error.value = err.response?.data?.message || 'Error al rastrear pedido';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Solicita reembolso de un pedido
     */
    const requestRefund = async (orderId, reason) => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.post(`/api/v1/orders/${orderId}/refund`, {
                reason
            });

            // Actualizar pedido en la lista
            const index = orders.value.findIndex(o => o.id === orderId);
            if (index !== -1) {
                orders.value[index] = response.data.data;
            }

            // Actualizar pedido actual si es el mismo
            if (currentOrder.value?.id === orderId) {
                currentOrder.value = response.data.data;
            }

            return {
                success: true,
                data: response.data.data,
                message: response.data.message || 'Reembolso solicitado exitosamente'
            };
        } catch (err) {
            if (err.response?.status === 401) {
                router.visit(route('login'));
                return { success: false, message: 'No autenticado' };
            }

            const errorMessage = err.response?.data?.message || 'Error al solicitar reembolso';
            error.value = errorMessage;

            return {
                success: false,
                message: errorMessage
            };
        } finally {
            loading.value = false;
        }
    };

    /**
     * Busca pedidos por término de búsqueda
     */
    const searchOrders = async (searchTerm) => {
        return fetchOrders({ search: searchTerm });
    };

    /**
     * Filtra pedidos por estado
     */
    const filterByStatus = async (status) => {
        return fetchOrders({ status });
    };

    /**
     * Formatea el estado del pedido a texto legible
     */
    const getStatusLabel = (status) => {
        const labels = {
            pending: 'Pendiente',
            processing: 'Procesando',
            shipped: 'Enviado',
            delivered: 'Entregado',
            cancelled: 'Cancelado',
            refunded: 'Reembolsado'
        };
        return labels[status] || status;
    };

    /**
     * Obtiene las clases CSS para el badge de estado
     */
    const getStatusColor = (status) => {
        const colors = {
            pending: 'bg-amber-100 text-amber-800 dark:bg-amber-900/20 dark:text-amber-400',
            processing: 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
            shipped: 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400',
            delivered: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
            cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
            refunded: 'bg-slate-100 text-slate-800 dark:bg-slate-900/20 dark:text-slate-400'
        };
        return colors[status] || 'bg-slate-100 text-slate-800';
    };

    /**
     * Verifica si un pedido puede ser cancelado
     */
    const canCancel = (order) => {
        return order && ['pending', 'processing'].includes(order.status);
    };

    /**
     * Verifica si un pedido puede ser reembolsado
     */
    const canRefund = (order) => {
        return order && ['processing', 'shipped', 'delivered'].includes(order.status) &&
               order.payment_status === 'completed';
    };

    /**
     * Verifica si un pedido puede ser rastreado
     */
    const canTrack = (order) => {
        return order && ['shipped', 'delivered'].includes(order.status);
    };

    /**
     * Limpia el estado actual
     */
    const clearState = () => {
        orders.value = [];
        currentOrder.value = null;
        stats.value = null;
        error.value = null;
    };

    // Propiedades computadas
    const hasOrders = computed(() => orders.value.length > 0);
    const isLoading = computed(() => loading.value);
    const hasError = computed(() => error.value !== null);

    return {
        // Estado
        orders,
        currentOrder,
        stats,
        loading,
        error,

        // Propiedades computadas
        hasOrders,
        isLoading,
        hasError,

        // Métodos
        fetchOrders,
        fetchOrder,
        createOrder,
        cancelOrder,
        fetchStats,
        trackOrder,
        requestRefund,
        searchOrders,
        filterByStatus,
        getStatusLabel,
        getStatusColor,
        canCancel,
        canRefund,
        canTrack,
        clearState
    };
}
