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
      // Escuchamos en todas las interfaces internas para que Nginx pueda alcanzar al dev server.
      host: '0.0.0.0',
      // Este puerto sólo existe dentro de Docker; externamente el navegador usará el 80 vía Nginx.
      port: 5174,
      // `strictPort` evita que Vite cambie el puerto y deje obsoleto el proxy configurado.
      strictPort: true,
      // Este origin publica la SPA con el dominio portless visible para el navegador.
      origin: `http://${devFrontendHost}`,
      allowedHosts: [
        // `localhost` se conserva para pruebas locales puntuales fuera del proxy.
        'localhost',
        // Este host habilita el dominio del frontend en producción si reutilizas la misma config.
        prodFrontendHost,
        // Este host habilita el dominio del frontend en el servidor de desarrollo.
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
