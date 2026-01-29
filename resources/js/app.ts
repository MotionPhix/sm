import '../css/app.css';
import 'vue-sonner/style.css'

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp } from 'vue';
import { renderApp, putConfig } from '@inertiaui/modal-vue'
import { initializeTheme } from '@/composables/useAppearance';

import { useMobileCheck } from '@/composables/useMobileCheck';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
const { isMobile } = useMobileCheck();

putConfig({
    type: 'modal',
    navigate: false,
    modal: {
        closeButton: true,
        closeExplicitly: false,
        maxWidth: 'xl',
        paddingClasses: 'p-0',
        panelClasses: '',
        position: isMobile ? 'center' : 'top',
    },
    slideover: {
        closeButton: true,
        closeExplicitly: false,
        maxWidth: 'md',
        paddingClasses: 'p-4 sm:p-6',
        panelClasses: 'bg-white min-h-screen',
        position: 'right',
    },
})

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: renderApp(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
