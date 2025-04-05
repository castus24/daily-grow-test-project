import {createRouter, createWebHistory} from 'vue-router'
import {useAuthStore} from "../stores/auth.js";

import Login from '../pages/auth/Login.vue';
import Register from '../pages/auth/Register.vue';

import Dashboard from '../pages/Dashboard.vue';
import Analytics from "../components/Analytics.vue";
import Clients from "../components/Clients.vue";
import Mailing from "../components/Mailing.vue"
import DashboardHome from "../components/DashboardHome.vue";
import Settings from "../components/Settings.vue";

const routes = [
    {
        path: '/login',
        name: 'login',
        component: Login,
    },
    {
        path: '/register',
        name: 'register',
        component: Register,
    },
    {
        path: '/',
        name: 'dashboard',
        component: Dashboard,
        meta: {requiresAuth: true},
        children: [
            {
                path: '',
                name: 'home',
                component: DashboardHome
            },
            {
                path: 'analytics',
                name: 'analytics',
                component: Analytics,
            },
            {
                path: 'clients',
                name: 'clients',
                component: Clients,
            },
            {
                path: 'mailing',
                name: 'mailing',
                component: Mailing,
            },
            {
                path: '/settings',
                name: 'settings',
                component: Settings,
            },
        ]
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore()

    if (!authStore.isAuthenticated && !authStore.loading) {
        await authStore.initializeAuth();
    }

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next({name: 'login'});
    } else {
        next();
    }
});

export default router
