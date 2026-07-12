import { createRouter, createWebHistory } from 'vue-router';
import Home from './pages/Home.vue';
import Analytics from './pages/analytics/Home.vue';
import Orders from './pages/orders/Home.vue';
import OrderDetail from './pages/orders/Detail.vue';
import BookingDetail from './pages/bookings/Detail.vue';
import PaymentDetail from './pages/payments/Detail.vue';
import InvoiceDetail from './pages/invoices/Detail.vue';
import FormSubmissionDetail from './pages/form-submissions/Detail.vue';
import Clients from './pages/clients/Home.vue';
import ClientDetail from './pages/clients/Detail.vue';
import ManagePage from './pages/page/Home.vue';
import PortfolioHome from './pages/page/portfolio/Home.vue';
import PortfolioCategories from './pages/page/portfolio/Categories.vue';
import PortfolioSettings from './pages/page/portfolio/Settings.vue';
import PortfolioDetail from './pages/page/portfolio/Detail.vue';
import BlogHome from './pages/page/blog/Home.vue';
import BlogCategories from './pages/page/blog/Categories.vue';
import BlogSettings from './pages/page/blog/Settings.vue';
import BlogDetail from './pages/page/blog/Detail.vue';
import StoreHome from './pages/page/store/Home.vue';
import StoreCategories from './pages/page/store/Categories.vue';
import StoreSettings from './pages/page/store/Settings.vue';
import StoreDetail from './pages/page/store/Detail.vue';
import MenuHome from './pages/page/menu/Home.vue';
import MenuCategories from './pages/page/menu/Categories.vue';
import MenuSettings from './pages/page/menu/Settings.vue';
import MenuDetail from './pages/page/menu/Detail.vue';
import StorePaymentOptions from './pages/page/store/PaymentOptions.vue';
import StoreShippingOptions from './pages/page/store/ShippingOptions.vue';
import ServicesHome from './pages/page/services/Home.vue';
import ServicesCategories from './pages/page/services/Categories.vue';
import ServicesCalendars from './pages/page/services/Calendars.vue';
import ServicesSettings from './pages/page/services/Settings.vue';
import ServicesDetail from './pages/page/services/Detail.vue';
import DigitalServicesHome from './pages/page/digital-services/Home.vue';
import DigitalServicesCategories from './pages/page/digital-services/Categories.vue';
import DigitalServicesSettings from './pages/page/digital-services/Settings.vue';
import DigitalServicesDetail from './pages/page/digital-services/Detail.vue';
import DigitalProductsHome from './pages/page/digital-products/Home.vue';
import DigitalProductsCategories from './pages/page/digital-products/Categories.vue';
import DigitalProductsSettings from './pages/page/digital-products/Settings.vue';
import DigitalProductsDetail from './pages/page/digital-products/Detail.vue';
import CoursesHome from './pages/page/courses/Home.vue';
import CoursesCategories from './pages/page/courses/Categories.vue';
import CoursesSettings from './pages/page/courses/Settings.vue';
import CoursesDetail from './pages/page/courses/Detail.vue';
import NewsletterHome from './pages/page/newsletter/Home.vue';
import NewsletterSettings from './pages/page/newsletter/Settings.vue';
import NewsletterDetail from './pages/page/newsletter/Detail.vue';
import FormsHome from './pages/page/forms/Home.vue';
import FormsDetail from './pages/page/forms/Detail.vue';
import UnitRentalHome from './pages/page/unit-rental/Home.vue';
import UnitRentalCategories from './pages/page/unit-rental/Categories.vue';
import UnitRentalCalendars from './pages/page/unit-rental/Calendars.vue';
import UnitRentalSettings from './pages/page/unit-rental/Settings.vue';
import UnitRentalDetail from './pages/page/unit-rental/Detail.vue';
import PagesHome from './pages/page/pages/Home.vue';
import PagesDetail from './pages/page/pages/Detail.vue';
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
    { path: '/analytics', name: 'analytics', component: Analytics },
    { path: '/orders', name: 'orders', component: Orders },
    { path: '/orders/:uuid', name: 'order-detail', component: OrderDetail },
    { path: '/bookings/:id', name: 'booking-detail', component: BookingDetail },
    { path: '/payments/:uuid', name: 'payment-detail', component: PaymentDetail },
    { path: '/invoices/:uuid', name: 'invoice-detail', component: InvoiceDetail },
    { path: '/form-submissions/:id', name: 'form-submission-detail', component: FormSubmissionDetail },
    { path: '/clients', name: 'clients', component: Clients },
    { path: '/clients/:uuid', name: 'client-detail', component: ClientDetail },
    { path: '/manage', name: 'manage-home', component: ManagePage },

    // Portfolio — dedicated pages (before generic /manage/:type catch-alls).
    { path: '/manage/portfolio', name: 'portfolio-home', component: PortfolioHome },
    { path: '/manage/portfolio/categories', name: 'portfolio-categories', component: PortfolioCategories },
    { path: '/manage/portfolio/settings', name: 'portfolio-settings', component: PortfolioSettings },
    { path: '/manage/portfolio/detail/:id', name: 'portfolio-detail', component: PortfolioDetail },

    // Blog — dedicated pages (before generic /manage/:type catch-alls).
    { path: '/manage/blog', name: 'blog-home', component: BlogHome },
    { path: '/manage/blog/categories', name: 'blog-categories', component: BlogCategories },
    { path: '/manage/blog/settings', name: 'blog-settings', component: BlogSettings },
    { path: '/manage/blog/detail/:id', name: 'blog-detail', component: BlogDetail },

    // Store — dedicated pages (before generic /manage/:type catch-alls).
    { path: '/manage/store', name: 'store-home', component: StoreHome },
    { path: '/manage/store/categories', name: 'store-categories', component: StoreCategories },
    { path: '/manage/store/settings', name: 'store-settings', component: StoreSettings },
    { path: '/manage/store/payment-options', name: 'store-payment-options', component: StorePaymentOptions },
    { path: '/manage/store/shipping-options', name: 'store-shipping-options', component: StoreShippingOptions },
    { path: '/manage/store/detail/:id', name: 'store-detail', component: StoreDetail },

    // Menu — dedicated pages (before generic /manage/:type catch-alls).
    { path: '/manage/menu', name: 'menu-home', component: MenuHome },
    { path: '/manage/menu/categories', name: 'menu-categories', component: MenuCategories },
    { path: '/manage/menu/settings', name: 'menu-settings', component: MenuSettings },
    { path: '/manage/menu/detail/:id', name: 'menu-detail', component: MenuDetail },

    // Services — dedicated pages (before generic /manage/:type catch-alls).
    { path: '/manage/services', name: 'services-home', component: ServicesHome },
    { path: '/manage/services/categories', name: 'services-categories', component: ServicesCategories },
    { path: '/manage/services/calendars', name: 'services-calendars', component: ServicesCalendars },
    { path: '/manage/services/settings', name: 'services-settings', component: ServicesSettings },
    { path: '/manage/services/detail/:id', name: 'services-detail', component: ServicesDetail },

    // Digital services — dedicated pages (before generic /manage/:type catch-alls).
    { path: '/manage/digital-services', name: 'digital-services-home', component: DigitalServicesHome },
    { path: '/manage/digital-services/categories', name: 'digital-services-categories', component: DigitalServicesCategories },
    { path: '/manage/digital-services/settings', name: 'digital-services-settings', component: DigitalServicesSettings },
    { path: '/manage/digital-services/detail/:id', name: 'digital-services-detail', component: DigitalServicesDetail },

    // Digital products — dedicated pages (before generic /manage/:type catch-alls).
    { path: '/manage/digital-products', name: 'digital-products-home', component: DigitalProductsHome },
    { path: '/manage/digital-products/categories', name: 'digital-products-categories', component: DigitalProductsCategories },
    { path: '/manage/digital-products/settings', name: 'digital-products-settings', component: DigitalProductsSettings },
    { path: '/manage/digital-products/detail/:id', name: 'digital-products-detail', component: DigitalProductsDetail },

    // Courses — dedicated pages (before generic /manage/:type catch-alls).
    { path: '/manage/courses', name: 'courses-home', component: CoursesHome },
    { path: '/manage/courses/categories', name: 'courses-categories', component: CoursesCategories },
    { path: '/manage/courses/settings', name: 'courses-settings', component: CoursesSettings },
    { path: '/manage/courses/detail/:id', name: 'courses-detail', component: CoursesDetail },

    // Newsletter — dedicated pages (before generic /manage/:type catch-alls).
    { path: '/manage/newsletter', name: 'newsletter-home', component: NewsletterHome },
    { path: '/manage/newsletter/settings', name: 'newsletter-settings', component: NewsletterSettings },
    { path: '/manage/newsletter/detail/:id', name: 'newsletter-detail', component: NewsletterDetail },

    // Forms — dedicated pages (before generic /manage/:type catch-alls).
    { path: '/manage/forms', name: 'forms-home', component: FormsHome },
    { path: '/manage/forms/detail/:id', name: 'forms-detail', component: FormsDetail },

    // Unit rental — dedicated pages (before generic /manage/:type catch-alls).
    { path: '/manage/unit-rental', name: 'unit-rental-home', component: UnitRentalHome },
    { path: '/manage/unit-rental/categories', name: 'unit-rental-categories', component: UnitRentalCategories },
    { path: '/manage/unit-rental/calendars', name: 'unit-rental-calendars', component: UnitRentalCalendars },
    { path: '/manage/unit-rental/settings', name: 'unit-rental-settings', component: UnitRentalSettings },
    { path: '/manage/unit-rental/detail/:id', name: 'unit-rental-detail', component: UnitRentalDetail },

    // Pages — dedicated pages (before generic /manage/:type catch-alls).
    { path: '/manage/pages', name: 'pages-home', component: PagesHome },
    { path: '/manage/pages/detail/:id', name: 'pages-detail', component: PagesDetail },

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
