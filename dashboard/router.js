import { createRouter, createWebHistory } from 'vue-router';
import Home from './pages/Home.vue';
import Orders from './pages/orders/Home.vue';
import OrderDetail from './pages/orders/Detail.vue';
import PaymentDetail from './pages/payments/Detail.vue';
import InvoiceDetail from './pages/invoices/Detail.vue';
import Clients from './pages/clients/Home.vue';
import ClientDetail from './pages/clients/Detail.vue';
import ManagePage from './pages/page/Home.vue';
import PortfolioHome from './pages/page/portfolio/Home.vue';
import PortfolioCategories from './pages/page/portfolio/Categories.vue';
import PortfolioSettings from './pages/page/portfolio/Settings.vue';
import PortfolioDetail from './pages/page/portfolio/Detail.vue';
import ContentIndex from './pages/page/ContentIndex.vue';
import ContentDetail from './pages/page/ContentDetail.vue';
import ContentCategories from './pages/page/ContentCategories.vue';
import ContentSettings from './pages/page/ContentSettings.vue';
import Settings from './pages/Settings.vue';
import SettingsGeneralInfo from './pages/settings/GeneralInfo.vue';
import SettingsDomain from './pages/settings/Domain.vue';
import SettingsAnalytics from './pages/settings/Analytics.vue';
import SettingsVerification from './pages/settings/Verification.vue';
import SettingsLanguageCurrency from './pages/settings/LanguageCurrency.vue';
import SettingsBranches from './pages/settings/Branches.vue';
import SettingsPaymentOptions from './pages/settings/PaymentOptions.vue';
import SettingsShippingOptions from './pages/settings/ShippingOptions.vue';
import Account from './pages/Account.vue';
import Plan from './pages/Plan.vue';
import NotFound from './pages/NotFound.vue';

const routes = [
    { path: '/', name: 'home', component: Home },
    { path: '/orders', name: 'orders', component: Orders },
    { path: '/orders/:uuid', name: 'order-detail', component: OrderDetail },
    { path: '/payments/:uuid', name: 'payment-detail', component: PaymentDetail },
    { path: '/invoices/:uuid', name: 'invoice-detail', component: InvoiceDetail },
    { path: '/clients', name: 'clients', component: Clients },
    { path: '/clients/:uuid', name: 'client-detail', component: ClientDetail },
    { path: '/manage', name: 'manage-home', component: ManagePage },

    // Portfolio — dedicated pages (before generic /manage/:type catch-alls).
    { path: '/manage/portfolio', name: 'portfolio-home', component: PortfolioHome },
    { path: '/manage/portfolio/categories', name: 'portfolio-categories', component: PortfolioCategories },
    { path: '/manage/portfolio/settings', name: 'portfolio-settings', component: PortfolioSettings },
    { path: '/manage/portfolio/detail/:id', name: 'portfolio-detail', component: PortfolioDetail },

    // Generic content-type stubs (other types until they get dedicated pages).
    { path: '/manage/:type', name: 'manage-index', component: ContentIndex },
    { path: '/manage/:type/detail/:id', name: 'manage-detail', component: ContentDetail },
    { path: '/manage/:type/categories', name: 'manage-categories', component: ContentCategories },
    { path: '/manage/:type/settings', name: 'manage-settings', component: ContentSettings },

    { path: '/settings', name: 'settings', component: Settings },
    { path: '/settings/general-info', name: 'settings-general-info', component: SettingsGeneralInfo },
    { path: '/settings/domain', name: 'settings-domain', component: SettingsDomain },
    { path: '/settings/analytics', name: 'settings-analytics', component: SettingsAnalytics },
    { path: '/settings/verification', name: 'settings-verification', component: SettingsVerification },
    { path: '/settings/language-currency', name: 'settings-language-currency', component: SettingsLanguageCurrency },
    { path: '/settings/branches', name: 'settings-branches', component: SettingsBranches },
    { path: '/settings/payment-options', name: 'settings-payment-options', component: SettingsPaymentOptions },
    { path: '/settings/shipping-option', name: 'settings-shipping-option', component: SettingsShippingOptions },

    { path: '/account', name: 'account', component: Account },
    { path: '/plan', name: 'plan', component: Plan },
    { path: '/:pathMatch(.*)*', name: 'not-found', component: NotFound },
];

// Base '/dashboard' matches the Laravel route that serves the SPA.
export default createRouter({
    history: createWebHistory('/dashboard'),
    routes,
});
