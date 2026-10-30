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
    }
 ]
});

export default router;

// import.meta.env.BASE_URL