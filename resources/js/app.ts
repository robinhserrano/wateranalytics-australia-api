import '../css/app.css';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { initializeTheme } from './composables/useAppearance';
import posthog from 'posthog-js';

import { ZiggyVue } from 'ziggy-js';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
const environment = import.meta.env.VITE_APP_ENV || 'local';

// Initialize PostHog
if (typeof window !== 'undefined' && import.meta.env.VITE_POSTHOG_KEY) {
    posthog.init(import.meta.env.VITE_POSTHOG_KEY, {
        api_host: import.meta.env.VITE_POSTHOG_HOST || 'https://us.i.posthog.com',
        person_profiles: 'always',
        capture_pageview: false, // We'll do this manually for Inertia
        autocapture: true,
    });

    posthog.register({
        environment: environment,
    });
}

// Track pageviews on Inertia navigation
router.on('navigate', () => {
    posthog.capture('$pageview');
});

// If the session/CSRF token has expired (e.g. a tab left open past
// SESSION_LIFETIME), Inertia's default behavior is to show a dead-end
// "419 | Page Expired" error overlay. Do a full reload instead: the current
// URL (filters and all) is preserved, and the reload fetches a fresh
// session/CSRF token so the user just needs to click the action again.
router.on('invalid', (event) => {
    if (event.detail.response.status === 419) {
        event.preventDefault();
        window.location.reload();
    }
});

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const user = props.initialPage.props.auth?.user;
        if (user) {
            posthog.identify(String(user.id), {
                email: user.email,
                name: user.name,
                environment: environment,
            });
        } else {
            posthog.reset();
        }

        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
