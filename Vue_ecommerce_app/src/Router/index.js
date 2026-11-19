import { createRouter, createWebHistory } from 'vue-router';

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
        path: '/',
        name: 'Welcome',
        component: () => import('../Pages/Welcome.vue'),
    },
    {
        path: '/login',
        name: 'Login',
        component: () => import('../Pages/Login.vue'),
    },
    {
        path: '/register',
        name: 'Register',
        component: () => import('../Pages/Register.vue'),
    },
    {
        path: '/verify-email',
        name: 'VerifyEmail',
        component: () => import('../Pages/VerifyEmail.vue'),
    },
    {
        path: '/reset-password',
        name: 'PasswordReset',
        component: () => import('../Pages/PasswordReset.vue'),
    }
 ]
});

export default router;

// import.meta.env.BASE_URL