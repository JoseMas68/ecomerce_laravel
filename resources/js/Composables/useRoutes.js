/**
 * Helper para rutas - Reemplazo temporal de Ziggy
 * TODO: Configurar Ziggy correctamente
 */
export function route(name, params = {}) {
    const routes = {
        'home': '/',
        'catalog.index': '/catalog',
        'catalog.show': (slug) => `/catalog/${slug}`,
        'cart.index': '/cart',
        'checkout.index': '/checkout',
        'login': '/auth/login',
        'login.post': '/auth/login',
        'register': '/auth/register',
        'register.post': '/auth/register',
        'logout': '/auth/logout',
        'password.request': '/password/reset',
        'profile.dashboard': '/profile',
        'profile.orders': '/profile/orders',
        'profile.orders.detail': (id) => `/profile/orders/${id}`,
        'profile.orders.track': (id) => `/profile/orders/${id}/track`,
        'admin.dashboard': '/admin',
        'admin.products.index': '/admin/products',
        'admin.products.create': '/admin/products/create',
        'admin.products.store': '/admin/products',
        'admin.products.edit': (id) => `/admin/products/${id}/edit`,
        'admin.products.update': (id) => `/admin/products/${id}`,
        'admin.products.destroy': (id) => `/admin/products/${id}`,
    };

    const routeFn = routes[name];

    if (typeof routeFn === 'function') {
        // Para rutas con parámetros como catalog.show
        if (name === 'catalog.show' && params.slug) {
            return routeFn(params.slug);
        }
        if (name === 'catalog.show') {
            return routeFn(params); // params puede ser el slug directamente
        }
        // Para rutas con parámetros como profile.orders.detail
        if ((name === 'profile.orders.detail' || name === 'profile.orders.track') && params.id) {
            return routeFn(params.id);
        }
        if ((name === 'profile.orders.detail' || name === 'profile.orders.track')) {
            return routeFn(params); // params puede ser el id directamente
        }
        if (name.startsWith('admin.products.') && params) {
            // Manejamos id si se pasa como objeto o directamente entero
            const id = typeof params === 'object' ? (params.product || params.id) : params;
            if (id) return routeFn(id);
        }
    }

    return routeFn || '#';
}
