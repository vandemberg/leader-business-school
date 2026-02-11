import './bootstrap';
import '../css/app.css';

import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
const ICON_SELECTORS = [
    '.material-symbols-outlined',
    '.material-symbols-rounded',
    '.material-symbols-sharp',
    '.material-icons',
];

function markIcons(root: ParentNode = document) {
    root.querySelectorAll(ICON_SELECTORS.join(',')).forEach((el) => {
        el.setAttribute('translate', 'no');
        el.classList.add('notranslate');
    });
}

function setupIconTranslationGuard() {
    if (typeof window === 'undefined') {
        return;
    }

    const w = window as typeof window & { __iconTranslateObserver__?: MutationObserver };
    if (w.__iconTranslateObserver__) {
        markIcons();
        return;
    }

    markIcons();

    const observer = new MutationObserver(() => {
        markIcons();
    });
    observer.observe(document.body, { childList: true, subtree: true });
    w.__iconTranslateObserver__ = observer;
}

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.tsx`, import.meta.glob('./Pages/**/*.tsx')),
    setup({ el, App, props }) {
        const root = createRoot(el);

        setupIconTranslationGuard();

        root.render(<App {...props} />);
    },
    progress: {
        color: '#4B5563',
    },
});
