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
        return resolvePageComponent(`./vue/pages/${name}.vue`, import.meta.glob('./vue/pages/**/*.vue'))
            .then((module) => {
                if (name.startsWith('PagesAdmin/')) {
                    module.default.layout = module.default.layout || AdminLayout;
                } else {
                    module.default.layout = module.default.layout || AppLayout;
                }
                return module;
            });
    },
    setup({ el, App, props }) {
        const app = createApp({ render: () => h(App, props) });

        // Reactive $t helper: always reads from latest page props
        app.config.globalProperties.$t = function (key) {
            // 'this' is the Vue component instance; access page via $page if available
            const translations = (this?.$page?.props?.translations)
                ?? props.initialPage.props.translations
                ?? {};
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
        app.mount(el);
    },
});

// Gérer l'erreur 419 (CSRF Token Mismatch / Session Expired) globalement
router.on('error', (event) => {
    if (event.detail.errors && Object.values(event.detail.errors).includes('419')) {
        window.location.reload();
    }
});

// Intercepter les erreurs de statut HTTP via l'événement 'finish' ou 'invalid' pour 419
router.on('invalid', (event) => {
    if (event.detail.response.status === 419) {
        window.location.reload();
    }
});