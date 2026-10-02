import { fileURLToPath, URL } from 'node:url'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'

// Este host representa el dominio público del frontend Vue en el servidor de desarrollo.
const devFrontendHost = 'cap-erp-ecommerce-dev.com'
// Este host adicional representa el dominio público productivo del frontend.
const prodFrontendHost = 'cap-erp-ecommerce.com'

// https://vite.dev/config/
export default defineConfig({
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
    // Esta configuración hace que el websocket HMR viaje por el puerto 80 público y no por 5174.
    hmr: {
      // El cliente HMR se conectará al mismo dominio público de la SPA.
      host: devFrontendHost,
      // El navegador usará el puerto 80 porque Nginx será el terminador público.
      clientPort: 80,
      // El websocket seguirá siendo `ws` porque en desarrollo no estamos terminando TLS aquí.
      protocol: 'ws',
    },
    watch: {
      // El polling hace más confiable la detección de cambios con volúmenes montados en Docker.
      usePolling: true,
    },
  },
})
