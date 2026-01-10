import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        hmr: {
            host: 'localhost',
            port: 5173,
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});

// import { defineConfig, loadEnv } from 'vite';
// import laravel from 'laravel-vite-plugin';

// export default defineConfig(({ mode }) => {
//     const env = loadEnv(mode, process.cwd(), '');

//     return {
//         server: {
//             host: '0.0.0.0',
//             port: env.VITE_HMR_PORT,
//             strictPort: true,
//             watch: {
//                 usePolling:'true',
//             },
//             hmr: {
//                 host: env.VITE_HMR_HOST,
//                 port: env.VITE_HMR_PORT,
//                 protocol: env.VITE_HMR_PROTOCOL,
//             }
//         },
//         plugins: [
//             laravel({
//                 input: ['resources/css/app.css', 'resources/js/app.js'],
//                 refresh: true,
//             }),
//         ],
//     };
// });
