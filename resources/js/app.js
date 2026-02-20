import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import './bootstrap';
import '../css/app.css';
import RoutePlugin from './Plugins/RoutePlugin';

createInertiaApp({
    title: (title) => `${title} - PawfectShop`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(RoutePlugin)
            .mount(el);
    },
    progress: {
        color: '#66e64c',
    },
});
