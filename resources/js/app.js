import './bootstrap';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { InertiaProgress } from '@inertiajs/progress';
import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';
import { definePreset } from '@primevue/themes';
import ToastService from 'primevue/toastservice';

import AppLayout from './vue/layout/AppLayout.vue';
import AdminLayout from './vue/layout/AdminLayout.vue';

const Noir = definePreset(Aura, {
    semantic: {
        primary: {
            50: '{zinc.50}',
            100: '{zinc.100}',
            200: '{zinc.200}',
            300: '{zinc.300}',
            400: '{zinc.400}',
            500: '{zinc.500}',
            600: '{zinc.600}',
            700: '{zinc.700}',
            800: '{zinc.800}',
            900: '{zinc.900}',
            950: '{zinc.950}'
        }
    }
});

import { router } from '@inertiajs/vue3';

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob('./vue/pages/**/*.vue', { eager: true });
        let module = pages[`./vue/pages/${name}.vue`];
        
        if (!module) throw new Error(`Page component not found: ${name}`);
        
        if (name.startsWith('PagesAdmin/')) {
            module.default.layout = module.default.layout || AdminLayout;
        } else {
            module.default.layout = module.default.layout || AppLayout;
        }
        return module;
    },
    setup({ el, App, props }) {
        const app = createApp({ render: () => h(App, props) });

        app.config.globalProperties.$t = function (key) {
            let translations = props.initialPage.props.translations ?? {};
            if (router && router.page && router.page.props && router.page.props.translations) {
                translations = router.page.props.translations;
            }
            return translations[key] ?? key;
        };

        app.use(PrimeVue, {
            theme: {
                preset: Noir,
                options: {
                    darkModeSelector: '.dark'
                }
            }
        });
        app.use(ToastService);

        // Helper global pour suivre les événements (clics sur boutons, cartes, etc.)
        app.config.globalProperties.$trackEvent = (eventName, params = {}) => {
            if (typeof window.gtag === 'function') {
                window.gtag('event', eventName, params);
            }
        };

        app.mount(el);
    },
});

// Suivi automatique des pages pour Inertia.js
router.on('navigate', (event) => {
    if (typeof window.gtag === 'function') {
        window.gtag('config', 'G-B4345195FJ', {
            page_path: event.detail.page.url,
            page_title: document.title,
        });
    }
});

router.on('error', (event) => {
    console.error('[INERTIA ERROR]', event.detail.errors || event);
    if (event.detail.errors && Object.values(event.detail.errors).includes('419')) {
        window.location.reload();
    }
});

router.on('invalid', (event) => {
    const status = event.detail.response.status;
    console.group(`[INERTIA INVALID-RESPONSE ${status}]`);
    console.error('URL:', event.detail.response.url);
    console.error('Data:', event.detail.response.data);
    console.groupEnd();

    if (status === 419) {
        window.location.reload();
    }
});

router.on('exception', (event) => {
    console.error('[INERTIA EXCEPTION]', event.detail.exception);
});