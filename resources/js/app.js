import './bootstrap';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { InertiaProgress } from '@inertiajs/progress';
import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';
import { definePreset } from '@primevue/themes';

import AppLayout from './vue/layout/AppLayout.vue';

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

createInertiaApp({
    resolve: (name) => {
        return resolvePageComponent(`./vue/pages/${name}.vue`, import.meta.glob('./vue/pages/**/*.vue'))
            .then((module) => {
                module.default.layout = module.default.layout || AppLayout;
                return module;
            });
    },
    setup({ el, App, props }) {
        const app = createApp({ render: () => h(App, props) });
        app.use(PrimeVue, {
            theme: {
                preset: Noir,
                options: {
                    darkModeSelector: '.dark'
                }
            }
        });
        app.mount(el);
    },
});

InertiaProgress.init();