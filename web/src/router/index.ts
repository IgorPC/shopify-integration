import { createRouter, createWebHistory } from 'vue-router';
import ProductList from '../views/ProductList.vue';
import SystemLogs from '../views/SystemLogs.vue';

const routes = [
    { path: '/', redirect: '/products' },
    { path: '/products', component: ProductList },
    { path: '/logs', component: SystemLogs },
];

export const router = createRouter({
    history: createWebHistory(),
    routes,
});