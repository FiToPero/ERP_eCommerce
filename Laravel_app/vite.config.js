import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig(({ mode }) => {
    // Cargamos variables de entorno sin prefijo para reutilizar la misma config entre dev y producción.
    const env = loadEnv(mode, process.cwd(), '');
    // Este host define el dominio público por el que el navegador hablará con el Vite server de Laravel.
    const viteHost = env.VITE_HMR_HOST || 'cap-erp-admin-dev.com';
    // Este puerto interno mantiene a Vite escuchando dentro del contenedor `node`.
    const vitePort = Number(env.VITE_HMR_PORT || 5173);
    // Este puerto visible desde el navegador apunta a Nginx, que luego reenvía al dev server interno.
    const viteClientPort = Number(env.VITE_HMR_CLIENT_PORT || 80);
    // Este path separa el websocket HMR del resto de rutas Laravel para que Nginx pueda proxyearlo.
    const viteHmrPath = env.VITE_HMR_PATH || '/vite-hmr';
    // Este origin fuerza a Laravel Vite a publicar assets bajo el dominio sin puerto del admin.
    const viteOrigin = env.VITE_DEV_SERVER_URL || `http://${viteHost}`;

    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js'],
                refresh: true,
            }),
            tailwindcss(),
        ],
        server: {
            // Escuchamos en todas las interfaces internas para que Nginx pueda llegar al contenedor.
            host: '0.0.0.0',
            // Este es el puerto real del proceso Vite dentro de Docker.
            port: vitePort,
            // Este origin genera tags de scripts y styles con el dominio público sin exponer `:5173`.
            origin: viteOrigin,
            // `strictPort` evita que Vite se mueva a otro puerto y rompa el proxy de Nginx.
            strictPort: true,
            allowedHosts: [
                // `localhost` sigue siendo útil para pruebas locales directas si alguna vez las necesitas.
                'localhost',
                // Este host permite el dominio productivo del panel si reutilizas esta config fuera de dev.
                'cap-erp-admin.com',
                // Este host habilita el dominio del panel en desarrollo detrás de Nginx.
                'cap-erp-admin-dev.com',
                // Este valor permite sobreescribir el host desde variables de entorno cuando haga falta.
                viteHost,
            ].filter(Boolean),
            hmr: {
                // Este host hace que el cliente HMR se conecte al dominio público del admin.
                host: viteHost,
                // Este puerto le dice al navegador que use el 80 público mientras Nginx reenvía internamente.
                clientPort: viteClientPort,
                // Este puerto interno mantiene la conexión real de Vite en 5173 dentro del contenedor.
                port: vitePort,
                // Este protocolo mantiene HMR por websocket en HTTP de desarrollo.
                protocol: env.VITE_HMR_PROTOCOL || 'ws',
                // Este path reserva una ruta clara para el websocket y evita mezclarlo con Laravel.
                path: viteHmrPath,
            },
            watch: {
                // Ignoramos vistas compiladas para evitar recargas infinitas causadas por archivos generados.
                ignored: ['**/storage/framework/views/**'],
                // El polling mejora compatibilidad con volúmenes Docker en servidores remotos.
                usePolling: true,
            },
        },
    };
});

