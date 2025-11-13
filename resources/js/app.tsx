import '../css/app.css';

import { createInertiaApp, router } from '@inertiajs/react';
import axios from 'axios';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import { toast } from 'sonner';
import { initializeTheme } from './hooks/use-appearance';

// Configurar axios para enviar CSRF token
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content');
}

router.on('error', (errors) => {
    // Erros da minha trait ThrowsExceptions`
    const validationErrors = errors?.detail?.errors;

    if (validationErrors && typeof validationErrors === 'object') {
        // Pega a primeira chave (ex: 'notes', 'name', etc.) // erros do Laravel Validation
        const firstField = Object.keys(validationErrors)[0];
        const firstMessage = validationErrors[firstField];

        if (firstMessage) {
            toast.error(firstMessage);
            return;
        }
    }

    toast.error('Erro inesperado.');
});

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ManagerClin`,
    resolve: (name) => resolvePageComponent(`./pages/${name}.tsx`, import.meta.glob('./pages/**/*.tsx')),
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(<App {...props} />);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on load...
initializeTheme();
