import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, DefineComponent, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { useTheme } from './composables/useTheme';
import { useSimpleI18n } from './composables/useSimpleI18n';
import i18n from './i18n';

const appName = import.meta.env.VITE_APP_NAME || 'Music Practice Tracker';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        // Инициализируем тему
        useTheme();
        
        // Инициализируем i18n
        try {
            const { loadLocale } = useSimpleI18n();
            loadLocale();
        } catch (error) {
            console.warn('Error initializing i18n:', error);
        }

        app.use(plugin)
            .use(ZiggyVue)
            .use(i18n)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
