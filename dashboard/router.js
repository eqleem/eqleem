import { createRouter, createWebHistory } from 'vue-router';

const routes = [
    { path: '/', name: 'home', component: () => import('./pages/Home.vue') },
    { path: '/analytics', name: 'analytics', component: () => import('./pages/analytics/Home.vue') },
    { path: '/orders', name: 'orders', component: () => import('./pages/orders/Home.vue') },
    { path: '/orders/:uuid', name: 'order-detail', component: () => import('./pages/orders/Detail.vue') },
    { path: '/bookings/:id', name: 'booking-detail', component: () => import('./pages/bookings/Detail.vue') },
    { path: '/payments/:uuid', name: 'payment-detail', component: () => import('./pages/payments/Detail.vue') },
    { path: '/invoices/:uuid', name: 'invoice-detail', component: () => import('./pages/invoices/Detail.vue') },
    { path: '/form-submissions/:id', name: 'form-submission-detail', component: () => import('./pages/form-submissions/Detail.vue') },
    { path: '/clients', name: 'clients', component: () => import('./pages/clients/Home.vue') },
    { path: '/clients/:uuid', name: 'client-detail', component: () => import('./pages/clients/Detail.vue') },
    { path: '/manage', name: 'manage-home', component: () => import('./pages/page/Home.vue') },

    // Portfolio — dedicated pages (before generic /manage/:type catch-alls).
    { path: '/manage/portfolio', name: 'portfolio-home', component: () => import('./pages/page/portfolio/Home.vue') },
    { path: '/manage/portfolio/categories', name: 'portfolio-categories', component: () => import('./pages/page/portfolio/Categories.vue') },
    { path: '/manage/portfolio/settings', name: 'portfolio-settings', component: () => import('./pages/page/portfolio/Settings.vue') },
    { path: '/manage/portfolio/detail/:id', name: 'portfolio-detail', component: () => import('./pages/page/portfolio/Detail.vue') },

    // Blog — dedicated pages (before generic /manage/:type catch-alls).
    { path: '/manage/blog', name: 'blog-home', component: () => import('./pages/page/blog/Home.vue') },
    { path: '/manage/blog/categories', name: 'blog-categories', component: () => import('./pages/page/blog/Categories.vue') },
    { path: '/manage/blog/settings', name: 'blog-settings', component: () => import('./pages/page/blog/Settings.vue') },
    { path: '/manage/blog/detail/:id', name: 'blog-detail', component: () => import('./pages/page/blog/Detail.vue') },

    // Store — dedicated pages (before generic /manage/:type catch-alls).
    { path: '/manage/store', name: 'store-home', component: () => import('./pages/page/store/Home.vue') },
    { path: '/manage/store/categories', name: 'store-categories', component: () => import('./pages/page/store/Categories.vue') },
    { path: '/manage/store/settings', name: 'store-settings', component: () => import('./pages/page/store/Settings.vue') },
    { path: '/manage/store/payment-options', name: 'store-payment-options', component: () => import('./pages/page/store/PaymentOptions.vue') },
    { path: '/manage/store/shipping-options', name: 'store-shipping-options', component: () => import('./pages/page/store/ShippingOptions.vue') },
    { path: '/manage/store/detail/:id', name: 'store-detail', component: () => import('./pages/page/store/Detail.vue') },

    // Menu — dedicated pages (before generic /manage/:type catch-alls).
    { path: '/manage/menu', name: 'menu-home', component: () => import('./pages/page/menu/Home.vue') },
    { path: '/manage/menu/categories', name: 'menu-categories', component: () => import('./pages/page/menu/Categories.vue') },
    { path: '/manage/menu/settings', name: 'menu-settings', component: () => import('./pages/page/menu/Settings.vue') },
    { path: '/manage/menu/detail/:id', name: 'menu-detail', component: () => import('./pages/page/menu/Detail.vue') },

    // Services — dedicated pages (before generic /manage/:type catch-alls).
    { path: '/manage/services', name: 'services-home', component: () => import('./pages/page/services/Home.vue') },
    { path: '/manage/services/categories', name: 'services-categories', component: () => import('./pages/page/services/Categories.vue') },
    { path: '/manage/services/calendars', name: 'services-calendars', component: () => import('./pages/page/services/Calendars.vue') },
    { path: '/manage/services/settings', name: 'services-settings', component: () => import('./pages/page/services/Settings.vue') },
    { path: '/manage/services/detail/:id', name: 'services-detail', component: () => import('./pages/page/services/Detail.vue') },

    // Digital services — dedicated pages (before generic /manage/:type catch-alls).
    { path: '/manage/digital-services', name: 'digital-services-home', component: () => import('./pages/page/digital-services/Home.vue') },
    { path: '/manage/digital-services/categories', name: 'digital-services-categories', component: () => import('./pages/page/digital-services/Categories.vue') },
    { path: '/manage/digital-services/settings', name: 'digital-services-settings', component: () => import('./pages/page/digital-services/Settings.vue') },
    { path: '/manage/digital-services/detail/:id', name: 'digital-services-detail', component: () => import('./pages/page/digital-services/Detail.vue') },

    // On-demand services — dedicated pages (before generic /manage/:type catch-alls).
    { path: '/manage/on-demand-services', name: 'on-demand-services-home', component: () => import('./pages/page/on-demand-services/Home.vue') },
    { path: '/manage/on-demand-services/settings', name: 'on-demand-services-settings', component: () => import('./pages/page/on-demand-services/Settings.vue') },
    { path: '/manage/on-demand-services/detail/:id', name: 'on-demand-services-detail', component: () => import('./pages/page/on-demand-services/Detail.vue') },

    // Digital products — dedicated pages (before generic /manage/:type catch-alls).
    { path: '/manage/digital-products', name: 'digital-products-home', component: () => import('./pages/page/digital-products/Home.vue') },
    { path: '/manage/digital-products/categories', name: 'digital-products-categories', component: () => import('./pages/page/digital-products/Categories.vue') },
    { path: '/manage/digital-products/settings', name: 'digital-products-settings', component: () => import('./pages/page/digital-products/Settings.vue') },
    { path: '/manage/digital-products/detail/:id', name: 'digital-products-detail', component: () => import('./pages/page/digital-products/Detail.vue') },

    // Courses — dedicated pages (before generic /manage/:type catch-alls).
    { path: '/manage/courses', name: 'courses-home', component: () => import('./pages/page/courses/Home.vue') },
    { path: '/manage/courses/categories', name: 'courses-categories', component: () => import('./pages/page/courses/Categories.vue') },
    { path: '/manage/courses/settings', name: 'courses-settings', component: () => import('./pages/page/courses/Settings.vue') },
    { path: '/manage/courses/detail/:id', name: 'courses-detail', component: () => import('./pages/page/courses/Detail.vue') },

    // Newsletter — dedicated pages (before generic /manage/:type catch-alls).
    { path: '/manage/newsletter', name: 'newsletter-home', component: () => import('./pages/page/newsletter/Home.vue') },
    { path: '/manage/newsletter/settings', name: 'newsletter-settings', component: () => import('./pages/page/newsletter/Settings.vue') },
    { path: '/manage/newsletter/detail/:id', name: 'newsletter-detail', component: () => import('./pages/page/newsletter/Detail.vue') },

    // Forms — dedicated pages (before generic /manage/:type catch-alls).
    { path: '/manage/forms', name: 'forms-home', component: () => import('./pages/page/forms/Home.vue') },
    { path: '/manage/forms/detail/:id', name: 'forms-detail', component: () => import('./pages/page/forms/Detail.vue') },

    // Unit rental — dedicated pages (before generic /manage/:type catch-alls).
    { path: '/manage/unit-rental', name: 'unit-rental-home', component: () => import('./pages/page/unit-rental/Home.vue') },
    { path: '/manage/unit-rental/categories', name: 'unit-rental-categories', component: () => import('./pages/page/unit-rental/Categories.vue') },
    { path: '/manage/unit-rental/calendars', name: 'unit-rental-calendars', component: () => import('./pages/page/unit-rental/Calendars.vue') },
    { path: '/manage/unit-rental/settings', name: 'unit-rental-settings', component: () => import('./pages/page/unit-rental/Settings.vue') },
    { path: '/manage/unit-rental/detail/:id', name: 'unit-rental-detail', component: () => import('./pages/page/unit-rental/Detail.vue') },

    // Pages — dedicated pages (before generic /manage/:type catch-alls).
    { path: '/manage/pages', name: 'pages-home', component: () => import('./pages/page/pages/Home.vue') },
    { path: '/manage/pages/contact/:id', name: 'pages-contact-detail', component: () => import('./pages/page/pages/ContactDetail.vue') },
    { path: '/manage/pages/faq/:id', name: 'pages-faq-detail', component: () => import('./pages/page/pages/FaqDetail.vue') },
    { path: '/manage/pages/about/:id', name: 'pages-about-detail', component: () => import('./pages/page/pages/AboutDetail.vue') },
    { path: '/manage/pages/detail/:id', name: 'pages-detail', component: () => import('./pages/page/pages/Detail.vue') },

    // Reviews has no categories; keep the old URL from landing on mock taxonomy UI.
    { path: '/manage/reviews/categories', redirect: { name: 'manage-index', params: { type: 'reviews' } } },

    // Generic content-type stubs (other types until they get dedicated pages).
    { path: '/manage/:type', name: 'manage-index', component: () => import('./pages/page/ContentIndex.vue') },
    { path: '/manage/:type/detail/:id', name: 'manage-detail', component: () => import('./pages/page/ContentDetail.vue') },
    { path: '/manage/:type/categories', name: 'manage-categories', component: () => import('./pages/page/ContentCategories.vue') },
    { path: '/manage/:type/settings', name: 'manage-settings', component: () => import('./pages/page/ContentSettings.vue') },

    { path: '/settings', name: 'settings', component: () => import('./pages/Settings.vue') },
    { path: '/settings/general-info', name: 'settings-general-info', component: () => import('./pages/settings/GeneralInfo.vue') },
    { path: '/settings/domain', name: 'settings-domain', component: () => import('./pages/settings/Domain.vue') },
    { path: '/settings/analytics', name: 'settings-analytics', component: () => import('./pages/settings/Analytics.vue') },
    { path: '/settings/verification', name: 'settings-verification', component: () => import('./pages/settings/Verification.vue') },
    { path: '/settings/language-currency', name: 'settings-language-currency', component: () => import('./pages/settings/LanguageCurrency.vue') },
    { path: '/settings/branches', name: 'settings-branches', component: () => import('./pages/settings/Branches.vue') },
    { path: '/settings/payment-options', name: 'settings-payment-options', component: () => import('./pages/settings/PaymentOptions.vue') },
    { path: '/settings/shipping-option', name: 'settings-shipping-option', component: () => import('./pages/settings/ShippingOptions.vue') },

    { path: '/account', name: 'account', component: () => import('./pages/Account.vue') },
    { path: '/plan', name: 'plan', component: () => import('./pages/Plan.vue') },
    { path: '/:pathMatch(.*)*', name: 'not-found', component: () => import('./pages/NotFound.vue') },
];

// Base '/dashboard' matches the Laravel route that serves the SPA.
export default createRouter({
    history: createWebHistory('/dashboard'),
    routes,
});
