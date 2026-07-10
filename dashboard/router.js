import { createRouter, createWebHistory } from 'vue-router';
import Home from './pages/Home.vue';
import Orders from './pages/Orders.vue';
import Clients from './pages/Clients.vue';
import ManagePage from './pages/ManagePage.vue';
import Settings from './pages/Settings.vue';
import SettingsDetail from './pages/SettingsDetail.vue';
import Account from './pages/Account.vue';
import Plan from './pages/Plan.vue';
import NotFound from './pages/NotFound.vue';

const routes = [
    { path: '/', name: 'home', component: Home },
    { path: '/orders', name: 'orders', component: Orders },
    { path: '/clients', name: 'clients', component: Clients },
    { path: '/manage-page', name: 'page', component: ManagePage },
    { path: '/settings', name: 'settings', component: Settings },
    { path: '/settings/:slug', name: 'settings-detail', component: SettingsDetail },
    { path: '/account', name: 'account', component: Account },
    { path: '/plan', name: 'plan', component: Plan },
    { path: '/:pathMatch(.*)*', name: 'not-found', component: NotFound },
];

// Base '/dashboard' matches the Laravel route that serves the SPA.
export default createRouter({
    history: createWebHistory('/dashboard'),
    routes,
});
