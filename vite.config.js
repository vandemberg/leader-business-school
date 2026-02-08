import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/sass/app.scss",
                "resources/js/app.tsx",
                "resources/js/bootstrap-blade.ts",
            ],
            ssr: "resources/js/ssr.tsx",
            refresh: true,
        }),
        react(),
    ],
    server: {
        host: "0.0.0.0",
        port: 5173,
        hmr: {
            host: "localhost",
        },
        watch: {
            usePolling: true,
        },
    },
});
