/**
 * Plugin de Vue para inyectar la función route() globalmente
 */
import { route } from '../Composables/useRoutes';

export default {
    install(app) {
        // Hacer route() disponible en todos los componentes
        app.config.globalProperties.route = route;

        // También proporcionar como inject (opcional)
        app.provide('route', () => route);
    }
};
