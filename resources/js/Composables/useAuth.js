import { ref, computed } from 'vue';
import axios from 'axios';

// Estado global de autenticación
const user = ref(null);
const token = ref(localStorage.getItem('auth_token'));

export function useAuth() {
    const isAuthenticated = computed(() => !!token.value && !!user.value);

    /**
     * Iniciar sesión
     */
    const login = async (credentials) => {
        try {
            const response = await axios.post('/api/v1/login', credentials);

            if (response.data.success) {
                const { user: userData, token: userToken } = response.data.data;

                // Guardar token y usuario
                token.value = userToken;
                user.value = userData;
                localStorage.setItem('auth_token', userToken);

                // Configurar axios para usar el token
                axios.defaults.headers.common['Authorization'] = `Bearer ${userToken}`;

                return { success: true, user: userData };
            }

            return { success: false, message: response.data.message };
        } catch (error) {
            console.error('Error en login:', error);
            return {
                success: false,
                message: error.response?.data?.message || 'Error al iniciar sesión'
            };
        }
    };

    /**
     * Registrar nuevo usuario
     */
    const register = async (userData) => {
        try {
            const response = await axios.post('/api/v1/register', userData);

            if (response.data.success) {
                const { user: newUser, token: userToken } = response.data.data;

                // Guardar token y usuario
                token.value = userToken;
                user.value = newUser;
                localStorage.setItem('auth_token', userToken);

                // Configurar axios
                axios.defaults.headers.common['Authorization'] = `Bearer ${userToken}`;

                return { success: true, user: newUser };
            }

            return { success: false, message: response.data.message };
        } catch (error) {
            console.error('Error en registro:', error);
            return {
                success: false,
                message: error.response?.data?.message || 'Error al registrar'
            };
        }
    };

    /**
     * Cerrar sesión
     */
    const logout = async () => {
        try {
            if (token.value) {
                await axios.post('/api/v1/logout');
            }
        } catch (error) {
            console.error('Error en logout:', error);
        } finally {
            // Limpiar estado
            token.value = null;
            user.value = null;
            localStorage.removeItem('auth_token');
            delete axios.defaults.headers.common['Authorization'];
        }
    };

    /**
     * Obtener perfil del usuario actual
     */
    const fetchUser = async () => {
        if (!token.value) return null;

        try {
            const response = await axios.get('/api/v1/me');
            if (response.data.success) {
                user.value = response.data.data;
                return user.value;
            }
        } catch (error) {
            console.error('Error al obtener usuario:', error);
            // Si hay error de autenticación, limpiar token
            if (error.response?.status === 401) {
                await logout();
            }
        }
        return null;
    };

    /**
     * Actualizar perfil
     */
    const updateProfile = async (data) => {
        try {
            const response = await axios.put('/api/v1/me', data);
            if (response.data.success) {
                user.value = response.data.data;
                return { success: true, user: user.value };
            }
            return { success: false, message: response.data.message };
        } catch (error) {
            console.error('Error al actualizar perfil:', error);
            return {
                success: false,
                message: error.response?.data?.message || 'Error al actualizar perfil'
            };
        }
    };

    /**
     * Cambiar contraseña
     */
    const changePassword = async (passwordData) => {
        try {
            const response = await axios.patch('/api/v1/me/password', passwordData);
            return { success: true, message: response.data.message };
        } catch (error) {
            console.error('Error al cambiar contraseña:', error);
            return {
                success: false,
                message: error.response?.data?.message || 'Error al cambiar contraseña'
            };
        }
    };

    // Inicializar: configurar axios si hay token guardado
    if (token.value) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
        fetchUser();
    }

    return {
        user,
        token,
        isAuthenticated,
        login,
        register,
        logout,
        fetchUser,
        updateProfile,
        changePassword
    };
}
