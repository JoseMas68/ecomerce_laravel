/**
 * Users Composable
 * Gestiona el estado y operaciones del perfil de usuario
 */
import { ref, computed } from 'vue';
import axios from 'axios';
import router from '@inertiajs/vue3';

// Estado reactivo
const profile = ref(null);
const addresses = ref([]);
const stats = ref(null);
const loading = ref(false);
const error = ref(null);

export function useUsers() {
    /**
     * Obtiene el perfil del usuario autenticado
     */
    const fetchProfile = async () => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.get('/api/v1/users/profile');
            profile.value = response.data.data;
            return response.data.data;
        } catch (err) {
            if (err.response?.status === 401) {
                router.visit(route('login'));
                return;
            }
            error.value = err.response?.data?.message || 'Error al cargar perfil';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Actualiza el perfil del usuario
     */
    const updateProfile = async (data) => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.put('/api/v1/users/profile', data);
            profile.value = response.data.data;

            return {
                success: true,
                data: response.data.data,
                message: response.data.message || 'Perfil actualizado exitosamente'
            };
        } catch (err) {
            if (err.response?.status === 401) {
                router.visit(route('login'));
                return { success: false, message: 'No autenticado' };
            }

            const errorMessage = err.response?.data?.message || 'Error al actualizar perfil';
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
     * Cambia la contraseña del usuario
     */
    const changePassword = async (passwords) => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.post('/api/v1/users/change-password', passwords);

            return {
                success: true,
                message: response.data.message || 'Contraseña cambiada exitosamente'
            };
        } catch (err) {
            if (err.response?.status === 401) {
                router.visit(route('login'));
                return { success: false, message: 'No autenticado' };
            }

            const errorMessage = err.response?.data?.message || 'Error al cambiar contraseña';
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
     * Sube el avatar del usuario
     */
    const uploadAvatar = async (file) => {
        loading.value = true;
        error.value = null;

        const formData = new FormData();
        formData.append('avatar', file);

        try {
            const response = await axios.post('/api/v1/users/upload-avatar', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });

            // Actualizar perfil con nueva URL de avatar
            if (profile.value) {
                profile.value.avatar = response.data.avatar_url;
            }

            return {
                success: true,
                avatarUrl: response.data.avatar_url,
                message: response.data.message || 'Avatar subido exitosamente'
            };
        } catch (err) {
            if (err.response?.status === 401) {
                router.visit(route('login'));
                return { success: false, message: 'No autenticado' };
            }

            const errorMessage = err.response?.data?.message || 'Error al subir avatar';
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
     * Elimina la cuenta del usuario
     */
    const deleteAccount = async (password) => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.delete('/api/v1/users/account', {
                data: { password }
            });

            return {
                success: true,
                message: response.data.message || 'Cuenta eliminada exitosamente'
            };
        } catch (err) {
            if (err.response?.status === 401) {
                router.visit(route('login'));
                return { success: false, message: 'No autenticado' };
            }

            const errorMessage = err.response?.data?.message || 'Error al eliminar cuenta';
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
     * Obtiene las direcciones del usuario
     */
    const fetchAddresses = async () => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.get('/api/v1/users/addresses');
            addresses.value = response.data.data || response.data;
            return response.data.data || response.data;
        } catch (err) {
            if (err.response?.status === 401) {
                router.visit(route('login'));
                return;
            }
            error.value = err.response?.data?.message || 'Error al cargar direcciones';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Crea una nueva dirección
     */
    const createAddress = async (addressData) => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.post('/api/v1/users/addresses', addressData);
            const newAddress = response.data.data;

            // Agregar a la lista
            addresses.value.push(newAddress);

            return {
                success: true,
                data: newAddress,
                message: response.data.message || 'Dirección creada exitosamente'
            };
        } catch (err) {
            if (err.response?.status === 401) {
                router.visit(route('login'));
                return { success: false, message: 'No autenticado' };
            }

            const errorMessage = err.response?.data?.message || 'Error al crear dirección';
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
     * Actualiza una dirección existente
     */
    const updateAddress = async (addressId, addressData) => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.put(`/api/v1/users/addresses/${addressId}`, addressData);
            const updatedAddress = response.data.data;

            // Actualizar en la lista
            const index = addresses.value.findIndex(a => a.id === addressId);
            if (index !== -1) {
                addresses.value[index] = updatedAddress;
            }

            return {
                success: true,
                data: updatedAddress,
                message: response.data.message || 'Dirección actualizada exitosamente'
            };
        } catch (err) {
            if (err.response?.status === 401) {
                router.visit(route('login'));
                return { success: false, message: 'No autenticado' };
            }

            const errorMessage = err.response?.data?.message || 'Error al actualizar dirección';
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
     * Elimina una dirección
     */
    const deleteAddress = async (addressId) => {
        loading.value = true;
        error.value = null;

        try {
            await axios.delete(`/api/v1/users/addresses/${addressId}`);

            // Eliminar de la lista
            addresses.value = addresses.value.filter(a => a.id !== addressId);

            return {
                success: true,
                message: 'Dirección eliminada exitosamente'
            };
        } catch (err) {
            if (err.response?.status === 401) {
                router.visit(route('login'));
                return { success: false, message: 'No autenticado' };
            }

            const errorMessage = err.response?.data?.message || 'Error al eliminar dirección';
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
     * Establece una dirección como default
     */
    const setDefaultAddress = async (addressId, type) => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.post(`/api/v1/users/addresses/${addressId}/set-default`, {
                type // 'shipping' o 'billing'
            });

            const updatedAddress = response.data.data;

            // Actualizar en la lista
            const index = addresses.value.findIndex(a => a.id === addressId);
            if (index !== -1) {
                addresses.value[index] = updatedAddress;
            }

            // Actualizar flags de otras direcciones
            addresses.value.forEach(addr => {
                if (type === 'shipping') {
                    addr.is_default_shipping = addr.id === addressId ? 1 : 0;
                } else if (type === 'billing') {
                    addr.is_default_billing = addr.id === addressId ? 1 : 0;
                }
            });

            return {
                success: true,
                data: updatedAddress,
                message: response.data.message || 'Dirección por defecto actualizada'
            };
        } catch (err) {
            if (err.response?.status === 401) {
                router.visit(route('login'));
                return { success: false, message: 'No autenticado' };
            }

            const errorMessage = err.response?.data?.message || 'Error al actualizar dirección';
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
            const response = await axios.get('/api/v1/users/stats');
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
     * Obtiene las iniciales del usuario
     */
    const getInitials = (user) => {
        if (!user) return '';

        const firstName = user.first_name || user.name || '';
        const lastName = user.last_name || '';

        if (firstName && lastName) {
            return `${firstName.charAt(0)}${lastName.charAt(0)}`.toUpperCase();
        }

        if (firstName) {
            return firstName.substring(0, 2).toUpperCase();
        }

        return user.email?.substring(0, 2).toUpperCase() || 'U';
    };

    /**
     * Obtiene el nombre completo del usuario
     */
    const getFullName = (user) => {
        if (!user) return '';
        const firstName = user.first_name || user.name || '';
        const lastName = user.last_name || '';
        return `${firstName} ${lastName}`.trim();
    };

    /**
     * Formatea la dirección completa
     */
    const formatAddress = (address) => {
        if (!address) return '';

        const parts = [
            address.address_line_1,
            address.address_line_2,
            `${address.postal_code} ${address.city}`,
            address.province,
            address.country || 'España'
        ].filter(Boolean);

        return parts.join(', ');
    };

    // Propiedades computadas
    const hasAddresses = computed(() => addresses.value.length > 0);
    const defaultShippingAddress = computed(() =>
        addresses.value.find(a => a.is_default_shipping) || null
    );
    const defaultBillingAddress = computed(() =>
        addresses.value.find(a => a.is_default_billing) || null
    );

    return {
        // Estado
        profile,
        addresses,
        stats,
        loading,
        error,

        // Propiedades computadas
        hasAddresses,
        defaultShippingAddress,
        defaultBillingAddress,

        // Métodos de perfil
        fetchProfile,
        updateProfile,
        changePassword,
        uploadAvatar,
        deleteAccount,

        // Métodos de direcciones
        fetchAddresses,
        createAddress,
        updateAddress,
        deleteAddress,
        setDefaultAddress,

        // Métodos de estadísticas
        fetchStats,

        // Helpers
        getInitials,
        getFullName,
        formatAddress
    };
}
