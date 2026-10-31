import { fileURLToPath, URL } from 'node:url'
import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '');
  // Este host representa el dominio público del frontend Vue en el servidor de desarrollo.
  const devFrontendHost = env.VITE_DEV_FRONTEND_HOST
  // Este host adicional representa el dominio público productivo del frontend.
  const prodFrontendHost = env.VITE_PROD_FRONTEND_HOST

  return {
    plugins: [vue(), tailwindcss()],
    resolve: {
      alias: {
        '@': fileURLToPath(new URL('./src', import.meta.url)),
      },
    },
    server: {
      host: '0.0.0.0',
      port: 5174,
      strictPort: true,
      // Este origin publica la SPA con el dominio portless visible para el navegador.
      origin: `http://${devFrontendHost}`,
      allowedHosts: [
        // `localhost` se conserva para pruebas locales puntuales fuera del proxy.
        'localhost',
        prodFrontendHost,
        devFrontendHost,
      ],
      hmr: {
        host: devFrontendHost,
        clientPort: 443,
        protocol: 'wss',

      },
      watch: {
        // El polling hace más confiable la detección de cambios con volúmenes montados en Docker.
        usePolling: true,
      },
    },
  };
})
