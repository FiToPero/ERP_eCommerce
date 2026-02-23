import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js'],
                refresh: true,
            }),
            tailwindcss(),
        ],
        server: {
            host: '0.0.0.0',
            port: env.VITE_HMR_PORT || 5173,
            strictPort: true,
            allowedHosts: [
                'localhost',
                'cap-erp-ecommer.com',
                env.VITE_HMR_HOST,
            ].filter(Boolean),
            hmr: {
                host: env.VITE_HMR_HOST || 'localhost',
                port: env.VITE_HMR_PORT || 5173,
                protocol: env.VITE_HMR_PROTOCOL || 'ws',
            },
            watch: {
                ignored: ['**/storage/framework/views/**'],
                usePolling: true,
            },
        },
    };
});

