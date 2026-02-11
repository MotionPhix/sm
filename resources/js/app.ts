import 'filepond/dist/filepond.min.css';
import 'vue-sonner/style.css';
import '../css/app.css';

import { initializeTheme } from '@/composables/useAppearance';
import { createInertiaApp } from '@inertiajs/vue3';
import { putConfig, renderApp } from '@inertiaui/modal-vue';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp } from 'vue';

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
