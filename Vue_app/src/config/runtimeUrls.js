// Este hostname intenta leer el dominio real desde el navegador cuando la SPA ya está cargada.
const currentHostname = typeof window !== 'undefined' ? window.location.hostname : ''

// Este flag detecta si la SPA se está ejecutando en el entorno de desarrollo basado en dominios `-dev`.
const isDevelopmentDomain = currentHostname === 'cap-erp-ecommerce-dev.com' || currentHostname === 'cap-erp-admin-dev.com'

// Este origen define el dominio base del admin de Laravel cuando no se suministró ninguna variable Vite explícita.
export const defaultAdminOrigin = isDevelopmentDomain ? 'http://cap-erp-admin-dev.com' : 'https://cap-erp-admin.com'

// Este origen define la URL base de la API pública de Laravel respetando el dominio del entorno actual.
export const defaultApiOrigin = `${defaultAdminOrigin}/api`

// Esta URL completa apunta al login del panel para los accesos que la SPA redirige al admin.
export const defaultAdminLoginUrl = `${defaultAdminOrigin}/admin`