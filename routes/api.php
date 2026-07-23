<?php

use App\API\Analytics\GetAnalyticsOverview;
use App\API\Blog\CreateBlogCategory;
use App\API\Blog\CreateBlogPost;
use App\API\Blog\DeleteBlogCategory;
use App\API\Blog\DeleteBlogFeaturedImage;
use App\API\Blog\DeleteBlogPosts;
use App\API\Blog\GetBlogSettings;
use App\API\Blog\ListBlogCategories;
use App\API\Blog\ListBlogPosts;
use App\API\Blog\ReorderBlogCategories;
use App\API\Blog\ShowBlogPost;
use App\API\Blog\ToggleBlogPostActive;
use App\API\Blog\UpdateBlogCategory;
use App\API\Blog\UpdateBlogPost;
use App\API\Blog\UpdateBlogSettings;
use App\API\Blog\UploadBlogEditorImage;
use App\API\Blog\UploadBlogFeaturedImage;
use App\API\Bookings\CreateBooking;
use App\API\Bookings\GetBookingAvailability;
use App\API\Bookings\ListBookings;
use App\API\Bookings\ShowBooking;
use App\API\Clients\CreateClient;
use App\API\Clients\ListClientInvoices;
use App\API\Clients\ListClientOrders;
use App\API\Clients\ListClients;
use App\API\Clients\ShowClient;
use App\API\Courses\CreateCourse;
use App\API\Courses\CreateCourseCategory;
use App\API\Courses\DeleteCourseCategory;
use App\API\Courses\DeleteCourseCoverImage;
use App\API\Courses\DeleteCourseLessonFile;
use App\API\Courses\DeleteCourses;
use App\API\Courses\GetCourseSettings;
use App\API\Courses\ListCourseCategories;
use App\API\Courses\ListCourses;
use App\API\Courses\ReorderCourseCategories;
use App\API\Courses\ShowCourse;
use App\API\Courses\UpdateCourse;
use App\API\Courses\UpdateCourseCategory;
use App\API\Courses\UpdateCourseSettings;
use App\API\Courses\UploadCourseCoverImage;
use App\API\Courses\UploadCourseEditorImage;
use App\API\Courses\UploadCourseLessonFile;
use App\API\Dashboard\AddWelcomeSocialLink;
use App\API\Dashboard\DismissOnboardingWizard;
use App\API\Dashboard\GetDashboardChart;
use App\API\Dashboard\GetDashboardContext;
use App\API\Dashboard\GetDashboardStat;
use App\API\Dashboard\GetDashboardStats;
use App\API\Dashboard\GetOnboarding;
use App\API\Dashboard\GetWelcomeWidget;
use App\API\Dashboard\SaveOnboardingBusiness;
use App\API\Dashboard\SaveOnboardingCatalog;
use App\API\Dashboard\SaveOnboardingContact;
use App\API\Dashboard\SaveOnboardingGoal;
use App\API\Dashboard\SaveOnboardingIdentity;
use App\API\Dashboard\UpdateWelcomeBasicInfo;
use App\API\Dashboard\UpdateWelcomeContact;
use App\API\DigitalProducts\CreateDigitalProduct;
use App\API\DigitalProducts\CreateDigitalProductCategory;
use App\API\DigitalProducts\DeleteDigitalProductCategory;
use App\API\DigitalProducts\DeleteDigitalProductDownload;
use App\API\DigitalProducts\DeleteDigitalProductImage;
use App\API\DigitalProducts\DeleteDigitalProducts;
use App\API\DigitalProducts\GetDigitalProductSettings;
use App\API\DigitalProducts\ListDigitalProductCategories;
use App\API\DigitalProducts\ListDigitalProducts;
use App\API\DigitalProducts\ReorderDigitalProductCategories;
use App\API\DigitalProducts\ReorderDigitalProductDownloads;
use App\API\DigitalProducts\ReorderDigitalProductImages;
use App\API\DigitalProducts\ShowDigitalProduct;
use App\API\DigitalProducts\ToggleDigitalProductActive;
use App\API\DigitalProducts\UpdateDigitalProduct;
use App\API\DigitalProducts\UpdateDigitalProductCategory;
use App\API\DigitalProducts\UpdateDigitalProductSettings;
use App\API\DigitalProducts\UploadDigitalProductDownload;
use App\API\DigitalProducts\UploadDigitalProductEditorImage;
use App\API\DigitalProducts\UploadDigitalProductImage;
use App\API\DigitalServices\CloneDigitalService;
use App\API\DigitalServices\CreateDigitalService;
use App\API\DigitalServices\CreateDigitalServiceCategory;
use App\API\DigitalServices\DeleteDigitalServiceCategory;
use App\API\DigitalServices\DeleteDigitalServiceImage;
use App\API\DigitalServices\DeleteDigitalServices;
use App\API\DigitalServices\GetDigitalServiceSettings;
use App\API\DigitalServices\ListDigitalServiceCategories;
use App\API\DigitalServices\ListDigitalServices;
use App\API\DigitalServices\ReorderDigitalServiceCategories;
use App\API\DigitalServices\ReorderDigitalServiceImages;
use App\API\DigitalServices\ShowDigitalService;
use App\API\DigitalServices\ToggleDigitalServiceActive;
use App\API\DigitalServices\UpdateDigitalService;
use App\API\DigitalServices\UpdateDigitalServiceCategory;
use App\API\DigitalServices\UpdateDigitalServiceSettings;
use App\API\DigitalServices\UploadDigitalServiceEditorImage;
use App\API\DigitalServices\UploadDigitalServiceImage;
use App\API\Forms\CloneForm;
use App\API\Forms\CreateForm;
use App\API\Forms\DeleteForms;
use App\API\Forms\ListForms;
use App\API\Forms\ShowForm;
use App\API\Forms\ToggleFormActive;
use App\API\Forms\UpdateForm;
use App\API\FormSubmissions\ListFormSubmissions;
use App\API\FormSubmissions\ShowFormSubmission;
use App\API\Icons\SearchTablerIcons;
use App\API\Invoices\ListInvoices;
use App\API\Invoices\ShowInvoice;
use App\API\Menu\CloneMenuItem;
use App\API\Menu\CreateMenuCategory;
use App\API\Menu\CreateMenuItem;
use App\API\Menu\DeleteMenuCategory;
use App\API\Menu\DeleteMenuImage;
use App\API\Menu\DeleteMenuItems;
use App\API\Menu\GetMenuSettings;
use App\API\Menu\ListMenuCategories;
use App\API\Menu\ListMenuItems;
use App\API\Menu\ReorderMenuCategories;
use App\API\Menu\ReorderMenuImages;
use App\API\Menu\ShowMenuItem;
use App\API\Menu\ToggleMenuItemActive;
use App\API\Menu\UpdateMenuCategory;
use App\API\Menu\UpdateMenuItem;
use App\API\Menu\UpdateMenuSettings;
use App\API\Menu\UploadMenuEditorImage;
use App\API\Menu\UploadMenuImage;
use App\API\Newsletter\CreateNewsletter;
use App\API\Newsletter\DeleteNewsletterFeaturedImage;
use App\API\Newsletter\DeleteNewsletters;
use App\API\Newsletter\GetNewsletterSettings;
use App\API\Newsletter\ListNewsletters;
use App\API\Newsletter\ShowNewsletter;
use App\API\Newsletter\UpdateNewsletter;
use App\API\Newsletter\UpdateNewsletterSettings;
use App\API\Newsletter\UploadNewsletterEditorImage;
use App\API\Newsletter\UploadNewsletterFeaturedImage;
use App\API\OnDemandServices\CreateOnDemandService;
use App\API\OnDemandServices\DeleteOnDemandServiceImage;
use App\API\OnDemandServices\DeleteOnDemandServices;
use App\API\OnDemandServices\GetOnDemandServiceSettings;
use App\API\OnDemandServices\ListOnDemandServices;
use App\API\OnDemandServices\ReorderOnDemandServiceImages;
use App\API\OnDemandServices\ShowOnDemandService;
use App\API\OnDemandServices\ToggleOnDemandServiceActive;
use App\API\OnDemandServices\UpdateOnDemandService;
use App\API\OnDemandServices\UpdateOnDemandServiceSettings;
use App\API\OnDemandServices\UploadOnDemandServiceEditorImage;
use App\API\OnDemandServices\UploadOnDemandServiceImage;
use App\API\Orders\CreateOrder;
use App\API\Orders\CreateOrderContent;
use App\API\Orders\ListOrders;
use App\API\Orders\RecordOrderPayment;
use App\API\Orders\SearchOrderContent;
use App\API\Orders\ShowOrder;
use App\API\Orders\UpdateOrderStatus;
use App\API\Page\AddPageHeaderSocialLink;
use App\API\Page\CreatePageBlock;
use App\API\Page\DeletePageBlock;
use App\API\Page\DeletePageBlockLink;
use App\API\Page\DeletePageFooterDocument;
use App\API\Page\DeletePageHeaderSocialLink;
use App\API\Page\GetPageDesign;
use App\API\Page\GetPageSectionContentCounts;
use App\API\Page\GetPageStructure;
use App\API\Page\ListCatalogSections;
use App\API\Page\ListContentTypes;
use App\API\Page\ReorderPageBlockLinks;
use App\API\Page\ReorderPageBlocks;
use App\API\Page\ReorderPageFooterDocuments;
use App\API\Page\ReorderPageHeaderSocialLinks;
use App\API\Page\SaveCatalogSections;
use App\API\Page\SavePageThemeOptions;
use App\API\Page\SearchPageLinkContent;
use App\API\Page\SetDefaultPageTheme;
use App\API\Page\ShowPageBlock;
use App\API\Page\TogglePageBlockActive;
use App\API\Page\UpdatePageBlock;
use App\API\Page\UpdatePageHeaderSocialLink;
use App\API\Page\UpsertPageBlockLink;
use App\API\Page\UpsertPageFooterDocument;
use App\API\Pages\CreatePage;
use App\API\Pages\CreatePageBlock as StandaloneCreatePageBlock;
use App\API\Pages\DeletePageBlock as StandaloneDeletePageBlock;
use App\API\Pages\DeletePageHeroImage;
use App\API\Pages\DeletePages;
use App\API\Pages\ListPageBlocks;
use App\API\Pages\ListPages;
use App\API\Pages\ReorderPageBlocks as StandaloneReorderPageBlocks;
use App\API\Pages\ShowPage;
use App\API\Pages\ShowPageBlock as StandaloneShowPageBlock;
use App\API\Pages\TogglePageActive;
use App\API\Pages\TogglePageBlockActive as StandaloneTogglePageBlockActive;
use App\API\Pages\UpdatePage;
use App\API\Pages\UpdatePageBlock as StandaloneUpdatePageBlock;
use App\API\Pages\UploadPageBrandMarkImage;
use App\API\Pages\UploadPageEditorImage;
use App\API\Pages\UploadPageHeroImage;
use App\API\Payments\ListPayments;
use App\API\Payments\ShowPayment;
use App\API\Plan\GetPlanCheckout;
use App\API\Plan\ListPlans;
use App\API\Plan\SubscribeFreePlan;
use App\API\Portfolio\ClonePortfolioProject;
use App\API\Portfolio\CreatePortfolioCategory;
use App\API\Portfolio\CreatePortfolioProject;
use App\API\Portfolio\DeletePortfolioCategory;
use App\API\Portfolio\DeletePortfolioImage;
use App\API\Portfolio\DeletePortfolioProjects;
use App\API\Portfolio\GetPortfolioSettings;
use App\API\Portfolio\ListPortfolioCategories;
use App\API\Portfolio\ListPortfolioProjects;
use App\API\Portfolio\ReorderPortfolioCategories;
use App\API\Portfolio\ReorderPortfolioImages;
use App\API\Portfolio\ShowPortfolioProject;
use App\API\Portfolio\TogglePortfolioProjectActive;
use App\API\Portfolio\UpdatePortfolioCategory;
use App\API\Portfolio\UpdatePortfolioProject;
use App\API\Portfolio\UpdatePortfolioSettings;
use App\API\Portfolio\UploadPortfolioEditorImage;
use App\API\Portfolio\UploadPortfolioImage;
use App\API\Reviews\ListReviews;
use App\API\Services\CreateService;
use App\API\Services\CreateServiceCalendar;
use App\API\Services\CreateServiceCategory;
use App\API\Services\DeleteServiceCalendar;
use App\API\Services\DeleteServiceCategory;
use App\API\Services\DeleteServiceImage;
use App\API\Services\DeleteServices;
use App\API\Services\GetServiceSettings;
use App\API\Services\ListServiceCalendars;
use App\API\Services\ListServiceCategories;
use App\API\Services\ListServices;
use App\API\Services\ReorderServiceCategories;
use App\API\Services\ReorderServiceImages;
use App\API\Services\ShowService;
use App\API\Services\ShowServiceCalendar;
use App\API\Services\ToggleServiceActive;
use App\API\Services\UpdateService;
use App\API\Services\UpdateServiceCalendar;
use App\API\Services\UpdateServiceCategory;
use App\API\Services\UpdateServiceSettings;
use App\API\Services\UploadServiceEditorImage;
use App\API\Services\UploadServiceImage;
use App\API\Settings\AddGeneralInfoSocialLink;
use App\API\Settings\CreateBranch;
use App\API\Settings\CreateCustomShippingOption;
use App\API\Settings\DeleteBranch;
use App\API\Settings\DeleteCustomShippingOption;
use App\API\Settings\DeleteGeneralInfoSocialLink;
use App\API\Settings\GetAnalyticsSettings;
use App\API\Settings\GetGeneralInfoSettings;
use App\API\Settings\GetLanguageCurrencySettings;
use App\API\Settings\GetVerificationSettings;
use App\API\Settings\ListBranches;
use App\API\Settings\ListPaymentOptions;
use App\API\Settings\ListShippingOptions;
use App\API\Settings\UpdateAnalyticsSettings;
use App\API\Settings\UpdateBranch;
use App\API\Settings\UpdateCustomShippingOption;
use App\API\Settings\UpdateCustomShippingOptionActive;
use App\API\Settings\UpdateGeneralInfoBasic;
use App\API\Settings\UpdateGeneralInfoContact;
use App\API\Settings\UpdateLanguageCurrencySettings;
use App\API\Settings\UpdatePaymentOptionActive;
use App\API\Settings\UpdatePaymentOptionSettings;
use App\API\Settings\UpdateShippingMethodActive;
use App\API\Settings\UpdateShippingMethodSettings;
use App\API\Settings\UpdateTenantCustomDomain;
use App\API\Settings\UpdateTenantHandle;
use App\API\Settings\UpdateVerificationSettings;
use App\API\Store\CreateStoreCategory;
use App\API\Store\CreateStoreProduct;
use App\API\Store\DeleteStoreCategory;
use App\API\Store\DeleteStoreImage;
use App\API\Store\DeleteStoreProducts;
use App\API\Store\GetStoreSettings;
use App\API\Store\ListStoreCategories;
use App\API\Store\ListStoreProducts;
use App\API\Store\ReorderStoreCategories;
use App\API\Store\ReorderStoreImages;
use App\API\Store\ShowStoreProduct;
use App\API\Store\ToggleStoreProductActive;
use App\API\Store\UpdateStoreCategory;
use App\API\Store\UpdateStoreProduct;
use App\API\Store\UpdateStoreSettings;
use App\API\Store\UploadStoreEditorImage;
use App\API\Store\UploadStoreImage;
use App\API\Tenants\CreateUserTenant;
use App\API\Tenants\ListUserTenants;
use App\API\Tenants\SwitchUserTenant;
use App\API\UnitRental\CloneUnitRental;
use App\API\UnitRental\CreateUnitRental;
use App\API\UnitRental\CreateUnitRentalCalendar;
use App\API\UnitRental\CreateUnitRentalCategory;
use App\API\UnitRental\DeleteUnitRentalCalendar;
use App\API\UnitRental\DeleteUnitRentalCategory;
use App\API\UnitRental\DeleteUnitRentalImage;
use App\API\UnitRental\DeleteUnitRentals;
use App\API\UnitRental\GetUnitRentalSettings;
use App\API\UnitRental\ListUnitRentalCalendars;
use App\API\UnitRental\ListUnitRentalCategories;
use App\API\UnitRental\ListUnitRentals;
use App\API\UnitRental\ReorderUnitRentalCategories;
use App\API\UnitRental\ReorderUnitRentalImages;
use App\API\UnitRental\ShowUnitRental;
use App\API\UnitRental\ShowUnitRentalCalendar;
use App\API\UnitRental\ToggleUnitRentalActive;
use App\API\UnitRental\UpdateUnitRental;
use App\API\UnitRental\UpdateUnitRentalCalendar;
use App\API\UnitRental\UpdateUnitRentalCategory;
use App\API\UnitRental\UpdateUnitRentalSettings;
use App\API\UnitRental\UploadUnitRentalEditorImage;
use App\API\UnitRental\UploadUnitRentalImage;
use App\API\Unsplash\SearchUnsplashPhotos;
use App\API\Unsplash\SelectUnsplashPhoto;
use App\API\User\UpdateAccountPassword;
use App\API\User\UpdateAccountProfile;
use App\API\User\UploadAccountAvatar;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard/context', GetDashboardContext::class)
    ->name('api.dashboard.context');

Route::get('/dashboard/stats', GetDashboardStats::class)
    ->name('api.dashboard.stats');

Route::get('/dashboard/stats/{metric}', GetDashboardStat::class)
    ->name('api.dashboard.stats.show')
    ->whereIn('metric', ['orders', 'sales', 'visits', 'clients']);

Route::get('/dashboard/charts/{chart}', GetDashboardChart::class)
    ->name('api.dashboard.charts.show')
    ->whereIn('chart', ['orders', 'sales', 'visits', 'clients']);

Route::get('/analytics/overview', GetAnalyticsOverview::class)
    ->name('api.analytics.overview');

Route::get('/dashboard/welcome', GetWelcomeWidget::class)
    ->name('api.dashboard.welcome');

Route::post('/dashboard/welcome/basic-info', UpdateWelcomeBasicInfo::class)
    ->name('api.dashboard.welcome.basic-info');

Route::put('/dashboard/welcome/contact', UpdateWelcomeContact::class)
    ->name('api.dashboard.welcome.contact');

Route::post('/dashboard/welcome/social', AddWelcomeSocialLink::class)
    ->name('api.dashboard.welcome.social');

Route::get('/dashboard/onboarding', GetOnboarding::class)
    ->name('api.dashboard.onboarding');

Route::post('/dashboard/onboarding/business', SaveOnboardingBusiness::class)
    ->name('api.dashboard.onboarding.business');

Route::put('/dashboard/onboarding/contact', SaveOnboardingContact::class)
    ->name('api.dashboard.onboarding.contact');

Route::put('/dashboard/onboarding/identity', SaveOnboardingIdentity::class)
    ->name('api.dashboard.onboarding.identity');

Route::put('/dashboard/onboarding/goal', SaveOnboardingGoal::class)
    ->name('api.dashboard.onboarding.goal');

Route::put('/dashboard/onboarding/catalog', SaveOnboardingCatalog::class)
    ->name('api.dashboard.onboarding.catalog');

Route::post('/dashboard/onboarding/dismiss', DismissOnboardingWizard::class)
    ->name('api.dashboard.onboarding.dismiss');

Route::get('/plans', ListPlans::class)
    ->name('api.plans.index');

Route::post('/plans/subscribe-free', SubscribeFreePlan::class)
    ->name('api.plans.subscribe-free');

Route::get('/plans/{plan}/checkout', GetPlanCheckout::class)
    ->name('api.plans.checkout')
    ->whereNumber('plan');

Route::get('/orders', ListOrders::class)
    ->name('api.orders.index');

Route::post('/orders', CreateOrder::class)
    ->name('api.orders.store');

Route::get('/bookings', ListBookings::class)
    ->name('api.bookings.index');

Route::post('/bookings', CreateBooking::class)
    ->name('api.bookings.store');

Route::get('/bookings/availability', GetBookingAvailability::class)
    ->name('api.bookings.availability');

Route::get('/bookings/{booking}', ShowBooking::class)
    ->name('api.bookings.show')
    ->whereNumber('booking');

Route::get('/orders/content-search', SearchOrderContent::class)
    ->name('api.orders.content-search');

Route::post('/orders/content', CreateOrderContent::class)
    ->name('api.orders.content.store');

Route::get('/orders/{uuid}', ShowOrder::class)
    ->name('api.orders.show')
    ->whereUuid('uuid');

Route::post('/orders/{uuid}/payments', RecordOrderPayment::class)
    ->name('api.orders.payments.store')
    ->whereUuid('uuid');

Route::patch('/orders/{uuid}/status', UpdateOrderStatus::class)
    ->name('api.orders.status.update')
    ->whereUuid('uuid');

Route::get('/payments', ListPayments::class)
    ->name('api.payments.index');

Route::get('/payments/{uuid}', ShowPayment::class)
    ->name('api.payments.show')
    ->whereUuid('uuid');

Route::get('/invoices', ListInvoices::class)
    ->name('api.invoices.index');

Route::get('/invoices/{uuid}', ShowInvoice::class)
    ->name('api.invoices.show')
    ->whereUuid('uuid');

Route::get('/form-submissions', ListFormSubmissions::class)
    ->name('api.form-submissions.index');

Route::get('/form-submissions/{id}', ShowFormSubmission::class)
    ->name('api.form-submissions.show')
    ->whereNumber('id');

Route::get('/reviews', ListReviews::class)
    ->name('api.reviews.index');

Route::get('/clients', ListClients::class)
    ->name('api.clients.index');

Route::post('/clients', CreateClient::class)
    ->name('api.clients.store');

Route::get('/clients/{uuid}', ShowClient::class)
    ->name('api.clients.show')
    ->whereUuid('uuid');

Route::get('/clients/{uuid}/orders', ListClientOrders::class)
    ->name('api.clients.orders.index')
    ->whereUuid('uuid');

Route::get('/clients/{uuid}/invoices', ListClientInvoices::class)
    ->name('api.clients.invoices.index')
    ->whereUuid('uuid');

Route::put('/account/profile', UpdateAccountProfile::class)
    ->name('api.account.profile.update');

Route::post('/account/avatar', UploadAccountAvatar::class)
    ->name('api.account.avatar.upload');

Route::put('/account/password', UpdateAccountPassword::class)
    ->name('api.account.password.update');

Route::get('/tenants', ListUserTenants::class)
    ->name('api.tenants.index');

Route::post('/tenants', CreateUserTenant::class)
    ->name('api.tenants.store');

Route::post('/tenants/{tenant}/switch', SwitchUserTenant::class)
    ->name('api.tenants.switch')
    ->whereNumber('tenant');

Route::put('/settings/domain/handle', UpdateTenantHandle::class)
    ->name('api.settings.domain.handle');

Route::put('/settings/domain/custom', UpdateTenantCustomDomain::class)
    ->name('api.settings.domain.custom');

Route::get('/settings/language-currency', GetLanguageCurrencySettings::class)
    ->name('api.settings.language-currency.show');

Route::put('/settings/language-currency', UpdateLanguageCurrencySettings::class)
    ->name('api.settings.language-currency.update');

Route::get('/settings/analytics', GetAnalyticsSettings::class)
    ->name('api.settings.analytics.show');

Route::put('/settings/analytics', UpdateAnalyticsSettings::class)
    ->name('api.settings.analytics.update');

Route::get('/settings/general-info', GetGeneralInfoSettings::class)
    ->name('api.settings.general-info.show');

Route::match(['put', 'post'], '/settings/general-info/basic', UpdateGeneralInfoBasic::class)
    ->name('api.settings.general-info.basic');

Route::put('/settings/general-info/contact', UpdateGeneralInfoContact::class)
    ->name('api.settings.general-info.contact');

Route::post('/settings/general-info/social', AddGeneralInfoSocialLink::class)
    ->name('api.settings.general-info.social.store');

Route::delete('/settings/general-info/social/{id}', DeleteGeneralInfoSocialLink::class)
    ->name('api.settings.general-info.social.destroy');

Route::get('/settings/verification', GetVerificationSettings::class)
    ->name('api.settings.verification.show');

Route::post('/settings/verification', UpdateVerificationSettings::class)
    ->name('api.settings.verification.update');

Route::get('/settings/branches', ListBranches::class)
    ->name('api.settings.branches.index');

Route::post('/settings/branches', CreateBranch::class)
    ->name('api.settings.branches.store');

Route::put('/settings/branches/{id}', UpdateBranch::class)
    ->name('api.settings.branches.update')
    ->whereNumber('id');

Route::delete('/settings/branches/{id}', DeleteBranch::class)
    ->name('api.settings.branches.destroy')
    ->whereNumber('id');

Route::get('/settings/payment-options', ListPaymentOptions::class)
    ->name('api.settings.payment-options.index');

Route::put('/settings/payment-options/{slug}/active', UpdatePaymentOptionActive::class)
    ->name('api.settings.payment-options.active');

Route::put('/settings/payment-options/{slug}', UpdatePaymentOptionSettings::class)
    ->name('api.settings.payment-options.update');

Route::get('/settings/shipping-options', ListShippingOptions::class)
    ->name('api.settings.shipping-options.index');

Route::put('/settings/shipping-options/methods/{slug}/active', UpdateShippingMethodActive::class)
    ->name('api.settings.shipping-options.methods.active');

Route::put('/settings/shipping-options/methods/{slug}', UpdateShippingMethodSettings::class)
    ->name('api.settings.shipping-options.methods.update');

Route::post('/settings/shipping-options/custom', CreateCustomShippingOption::class)
    ->name('api.settings.shipping-options.custom.store');

Route::put('/settings/shipping-options/custom/{id}', UpdateCustomShippingOption::class)
    ->name('api.settings.shipping-options.custom.update');

Route::delete('/settings/shipping-options/custom/{id}', DeleteCustomShippingOption::class)
    ->name('api.settings.shipping-options.custom.destroy');

Route::put('/settings/shipping-options/custom/{id}/active', UpdateCustomShippingOptionActive::class)
    ->name('api.settings.shipping-options.custom.active');

Route::get('/page/structure', GetPageStructure::class)
    ->name('api.page.structure');

Route::get('/page/section-content-counts', GetPageSectionContentCounts::class)
    ->name('api.page.section-content-counts');

Route::get('/page/content-types', ListContentTypes::class)
    ->name('api.page.content-types');

Route::get('/page/catalog-sections', ListCatalogSections::class)
    ->name('api.page.catalog-sections');

Route::put('/page/catalog-sections', SaveCatalogSections::class)
    ->name('api.page.catalog-sections.update');

Route::get('/page/design', GetPageDesign::class)
    ->name('api.page.design');

Route::put('/page/design/theme', SetDefaultPageTheme::class)
    ->name('api.page.design.theme');

Route::match(['put', 'post'], '/page/design/options', SavePageThemeOptions::class)
    ->name('api.page.design.options');

Route::get('/unsplash/photos', SearchUnsplashPhotos::class)
    ->name('api.unsplash.photos');

Route::post('/unsplash/photos/select', SelectUnsplashPhoto::class)
    ->name('api.unsplash.photos.select');

Route::post('/page/blocks', CreatePageBlock::class)
    ->name('api.page.blocks.store');

Route::put('/page/blocks/reorder', ReorderPageBlocks::class)
    ->name('api.page.blocks.reorder');

Route::get('/page/blocks/{id}', ShowPageBlock::class)
    ->name('api.page.blocks.show')
    ->whereNumber('id');

Route::match(['put', 'post'], '/page/blocks/{id}', UpdatePageBlock::class)
    ->name('api.page.blocks.update')
    ->whereNumber('id');

Route::put('/page/blocks/{id}/active', TogglePageBlockActive::class)
    ->name('api.page.blocks.active')
    ->whereNumber('id');

Route::delete('/page/blocks/{id}', DeletePageBlock::class)
    ->name('api.page.blocks.destroy')
    ->whereNumber('id');

Route::post('/page/blocks/{id}/links', UpsertPageBlockLink::class)
    ->name('api.page.blocks.links.store')
    ->whereNumber('id');

Route::put('/page/blocks/{id}/links/reorder', ReorderPageBlockLinks::class)
    ->name('api.page.blocks.links.reorder')
    ->whereNumber('id');

Route::match(['put', 'post'], '/page/blocks/{id}/links/{linkId}', UpsertPageBlockLink::class)
    ->name('api.page.blocks.links.update')
    ->whereNumber('id')
    ->whereNumber('linkId');

Route::delete('/page/blocks/{id}/links/{linkId}', DeletePageBlockLink::class)
    ->name('api.page.blocks.links.destroy')
    ->whereNumber('id')
    ->whereNumber('linkId');

Route::post('/page/blocks/{id}/footer-documents', UpsertPageFooterDocument::class)
    ->name('api.page.blocks.footer-documents.store')
    ->whereNumber('id');

Route::put('/page/blocks/{id}/footer-documents/reorder', ReorderPageFooterDocuments::class)
    ->name('api.page.blocks.footer-documents.reorder')
    ->whereNumber('id');

Route::match(['put', 'post'], '/page/blocks/{id}/footer-documents/{documentId}', UpsertPageFooterDocument::class)
    ->name('api.page.blocks.footer-documents.update')
    ->whereNumber('id');

Route::delete('/page/blocks/{id}/footer-documents/{documentId}', DeletePageFooterDocument::class)
    ->name('api.page.blocks.footer-documents.destroy')
    ->whereNumber('id');

Route::get('/icons/tabler', SearchTablerIcons::class)
    ->name('api.icons.tabler');

Route::get('/page/link-content', SearchPageLinkContent::class)
    ->name('api.page.link-content');

Route::post('/page/header/social', AddPageHeaderSocialLink::class)
    ->name('api.page.header.social.store');

Route::put('/page/header/social/reorder', ReorderPageHeaderSocialLinks::class)
    ->name('api.page.header.social.reorder');

Route::put('/page/header/social/{id}', UpdatePageHeaderSocialLink::class)
    ->name('api.page.header.social.update');

Route::delete('/page/header/social/{id}', DeletePageHeaderSocialLink::class)
    ->name('api.page.header.social.destroy');

Route::get('/portfolio', ListPortfolioProjects::class)
    ->name('api.portfolio.index');

Route::post('/portfolio', CreatePortfolioProject::class)
    ->name('api.portfolio.store');

Route::delete('/portfolio', DeletePortfolioProjects::class)
    ->name('api.portfolio.destroy');

Route::get('/portfolio/settings', GetPortfolioSettings::class)
    ->name('api.portfolio.settings.show');

Route::put('/portfolio/settings', UpdatePortfolioSettings::class)
    ->name('api.portfolio.settings.update');

Route::get('/portfolio/categories', ListPortfolioCategories::class)
    ->name('api.portfolio.categories.index');

Route::post('/portfolio/categories', CreatePortfolioCategory::class)
    ->name('api.portfolio.categories.store');

Route::put('/portfolio/categories/reorder', ReorderPortfolioCategories::class)
    ->name('api.portfolio.categories.reorder');

Route::put('/portfolio/categories/{id}', UpdatePortfolioCategory::class)
    ->name('api.portfolio.categories.update')
    ->whereNumber('id');

Route::delete('/portfolio/categories/{id}', DeletePortfolioCategory::class)
    ->name('api.portfolio.categories.destroy')
    ->whereNumber('id');

Route::get('/portfolio/{uuid}', ShowPortfolioProject::class)
    ->name('api.portfolio.show')
    ->whereUuid('uuid');

Route::put('/portfolio/{uuid}', UpdatePortfolioProject::class)
    ->name('api.portfolio.update')
    ->whereUuid('uuid');

Route::put('/portfolio/{uuid}/active', TogglePortfolioProjectActive::class)
    ->name('api.portfolio.active')
    ->whereUuid('uuid');

Route::post('/portfolio/{uuid}/clone', ClonePortfolioProject::class)
    ->name('api.portfolio.clone')
    ->whereUuid('uuid');

Route::post('/portfolio/{uuid}/images', UploadPortfolioImage::class)
    ->name('api.portfolio.images.store')
    ->whereUuid('uuid');

Route::put('/portfolio/{uuid}/images/reorder', ReorderPortfolioImages::class)
    ->name('api.portfolio.images.reorder')
    ->whereUuid('uuid');

Route::delete('/portfolio/{uuid}/images/{mediaId}', DeletePortfolioImage::class)
    ->name('api.portfolio.images.destroy')
    ->whereUuid('uuid')
    ->whereNumber('mediaId');

Route::post('/portfolio/{uuid}/editor-images', UploadPortfolioEditorImage::class)
    ->name('api.portfolio.editor-images.store')
    ->whereUuid('uuid');

Route::get('/blog', ListBlogPosts::class)
    ->name('api.blog.index');

Route::post('/blog', CreateBlogPost::class)
    ->name('api.blog.store');

Route::delete('/blog', DeleteBlogPosts::class)
    ->name('api.blog.destroy');

Route::get('/blog/settings', GetBlogSettings::class)
    ->name('api.blog.settings.show');

Route::put('/blog/settings', UpdateBlogSettings::class)
    ->name('api.blog.settings.update');

Route::get('/blog/categories', ListBlogCategories::class)
    ->name('api.blog.categories.index');

Route::post('/blog/categories', CreateBlogCategory::class)
    ->name('api.blog.categories.store');

Route::put('/blog/categories/reorder', ReorderBlogCategories::class)
    ->name('api.blog.categories.reorder');

Route::put('/blog/categories/{id}', UpdateBlogCategory::class)
    ->name('api.blog.categories.update')
    ->whereNumber('id');

Route::delete('/blog/categories/{id}', DeleteBlogCategory::class)
    ->name('api.blog.categories.destroy')
    ->whereNumber('id');

Route::get('/blog/{uuid}', ShowBlogPost::class)
    ->name('api.blog.show')
    ->whereUuid('uuid');

Route::put('/blog/{uuid}', UpdateBlogPost::class)
    ->name('api.blog.update')
    ->whereUuid('uuid');

Route::put('/blog/{uuid}/active', ToggleBlogPostActive::class)
    ->name('api.blog.active')
    ->whereUuid('uuid');

Route::post('/blog/{uuid}/featured-image', UploadBlogFeaturedImage::class)
    ->name('api.blog.featured-image.store')
    ->whereUuid('uuid');

Route::delete('/blog/{uuid}/featured-image', DeleteBlogFeaturedImage::class)
    ->name('api.blog.featured-image.destroy')
    ->whereUuid('uuid');

Route::post('/blog/{uuid}/editor-images', UploadBlogEditorImage::class)
    ->name('api.blog.editor-images.store')
    ->whereUuid('uuid');

Route::get('/store', ListStoreProducts::class)
    ->name('api.store.index');

Route::post('/store', CreateStoreProduct::class)
    ->name('api.store.store');

Route::delete('/store', DeleteStoreProducts::class)
    ->name('api.store.destroy');

Route::get('/store/settings', GetStoreSettings::class)
    ->name('api.store.settings.show');

Route::put('/store/settings', UpdateStoreSettings::class)
    ->name('api.store.settings.update');

Route::get('/store/categories', ListStoreCategories::class)
    ->name('api.store.categories.index');

Route::post('/store/categories', CreateStoreCategory::class)
    ->name('api.store.categories.store');

Route::put('/store/categories/reorder', ReorderStoreCategories::class)
    ->name('api.store.categories.reorder');

Route::put('/store/categories/{id}', UpdateStoreCategory::class)
    ->name('api.store.categories.update')
    ->whereNumber('id');

Route::delete('/store/categories/{id}', DeleteStoreCategory::class)
    ->name('api.store.categories.destroy')
    ->whereNumber('id');

Route::get('/store/{uuid}', ShowStoreProduct::class)
    ->name('api.store.show')
    ->whereUuid('uuid');

Route::put('/store/{uuid}', UpdateStoreProduct::class)
    ->name('api.store.update')
    ->whereUuid('uuid');

Route::put('/store/{uuid}/active', ToggleStoreProductActive::class)
    ->name('api.store.active')
    ->whereUuid('uuid');

Route::post('/store/{uuid}/images', UploadStoreImage::class)
    ->name('api.store.images.store')
    ->whereUuid('uuid');

Route::put('/store/{uuid}/images/reorder', ReorderStoreImages::class)
    ->name('api.store.images.reorder')
    ->whereUuid('uuid');

Route::delete('/store/{uuid}/images/{mediaId}', DeleteStoreImage::class)
    ->name('api.store.images.destroy')
    ->whereUuid('uuid')
    ->whereNumber('mediaId');

Route::post('/store/{uuid}/editor-images', UploadStoreEditorImage::class)
    ->name('api.store.editor-images.store')
    ->whereUuid('uuid');

Route::get('/menu', ListMenuItems::class)
    ->name('api.menu.index');

Route::post('/menu', CreateMenuItem::class)
    ->name('api.menu.store');

Route::delete('/menu', DeleteMenuItems::class)
    ->name('api.menu.destroy');

Route::get('/menu/settings', GetMenuSettings::class)
    ->name('api.menu.settings.show');

Route::put('/menu/settings', UpdateMenuSettings::class)
    ->name('api.menu.settings.update');

Route::get('/menu/categories', ListMenuCategories::class)
    ->name('api.menu.categories.index');

Route::post('/menu/categories', CreateMenuCategory::class)
    ->name('api.menu.categories.store');

Route::put('/menu/categories/reorder', ReorderMenuCategories::class)
    ->name('api.menu.categories.reorder');

Route::put('/menu/categories/{id}', UpdateMenuCategory::class)
    ->name('api.menu.categories.update')
    ->whereNumber('id');

Route::delete('/menu/categories/{id}', DeleteMenuCategory::class)
    ->name('api.menu.categories.destroy')
    ->whereNumber('id');

Route::get('/menu/{uuid}', ShowMenuItem::class)
    ->name('api.menu.show')
    ->whereUuid('uuid');

Route::put('/menu/{uuid}', UpdateMenuItem::class)
    ->name('api.menu.update')
    ->whereUuid('uuid');

Route::put('/menu/{uuid}/active', ToggleMenuItemActive::class)
    ->name('api.menu.active')
    ->whereUuid('uuid');

Route::post('/menu/{uuid}/clone', CloneMenuItem::class)
    ->name('api.menu.clone')
    ->whereUuid('uuid');

Route::post('/menu/{uuid}/images', UploadMenuImage::class)
    ->name('api.menu.images.store')
    ->whereUuid('uuid');

Route::put('/menu/{uuid}/images/reorder', ReorderMenuImages::class)
    ->name('api.menu.images.reorder')
    ->whereUuid('uuid');

Route::delete('/menu/{uuid}/images/{mediaId}', DeleteMenuImage::class)
    ->name('api.menu.images.destroy')
    ->whereUuid('uuid')
    ->whereNumber('mediaId');

Route::post('/menu/{uuid}/editor-images', UploadMenuEditorImage::class)
    ->name('api.menu.editor-images.store')
    ->whereUuid('uuid');

Route::get('/services', ListServices::class)
    ->name('api.services.index');

Route::post('/services', CreateService::class)
    ->name('api.services.store');

Route::delete('/services', DeleteServices::class)
    ->name('api.services.destroy');

Route::get('/services/settings', GetServiceSettings::class)
    ->name('api.services.settings.show');

Route::put('/services/settings', UpdateServiceSettings::class)
    ->name('api.services.settings.update');

Route::get('/services/categories', ListServiceCategories::class)
    ->name('api.services.categories.index');

Route::post('/services/categories', CreateServiceCategory::class)
    ->name('api.services.categories.store');

Route::put('/services/categories/reorder', ReorderServiceCategories::class)
    ->name('api.services.categories.reorder');

Route::put('/services/categories/{id}', UpdateServiceCategory::class)
    ->name('api.services.categories.update')
    ->whereNumber('id');

Route::delete('/services/categories/{id}', DeleteServiceCategory::class)
    ->name('api.services.categories.destroy')
    ->whereNumber('id');

Route::get('/services/calendars', ListServiceCalendars::class)
    ->name('api.services.calendars.index');

Route::post('/services/calendars', CreateServiceCalendar::class)
    ->name('api.services.calendars.store');

Route::get('/services/calendars/{id}', ShowServiceCalendar::class)
    ->name('api.services.calendars.show')
    ->whereNumber('id');

Route::put('/services/calendars/{id}', UpdateServiceCalendar::class)
    ->name('api.services.calendars.update')
    ->whereNumber('id');

Route::delete('/services/calendars/{id}', DeleteServiceCalendar::class)
    ->name('api.services.calendars.destroy')
    ->whereNumber('id');

Route::get('/services/{uuid}', ShowService::class)
    ->name('api.services.show')
    ->whereUuid('uuid');

Route::put('/services/{uuid}', UpdateService::class)
    ->name('api.services.update')
    ->whereUuid('uuid');

Route::put('/services/{uuid}/active', ToggleServiceActive::class)
    ->name('api.services.active')
    ->whereUuid('uuid');

Route::post('/services/{uuid}/images', UploadServiceImage::class)
    ->name('api.services.images.store')
    ->whereUuid('uuid');

Route::put('/services/{uuid}/images/reorder', ReorderServiceImages::class)
    ->name('api.services.images.reorder')
    ->whereUuid('uuid');

Route::delete('/services/{uuid}/images/{mediaId}', DeleteServiceImage::class)
    ->name('api.services.images.destroy')
    ->whereUuid('uuid')
    ->whereNumber('mediaId');

Route::post('/services/{uuid}/editor-images', UploadServiceEditorImage::class)
    ->name('api.services.editor-images.store')
    ->whereUuid('uuid');

Route::get('/unit-rental', ListUnitRentals::class)
    ->name('api.unit-rental.index');

Route::post('/unit-rental', CreateUnitRental::class)
    ->name('api.unit-rental.store');

Route::delete('/unit-rental', DeleteUnitRentals::class)
    ->name('api.unit-rental.destroy');

Route::get('/unit-rental/settings', GetUnitRentalSettings::class)
    ->name('api.unit-rental.settings.show');

Route::put('/unit-rental/settings', UpdateUnitRentalSettings::class)
    ->name('api.unit-rental.settings.update');

Route::get('/unit-rental/categories', ListUnitRentalCategories::class)
    ->name('api.unit-rental.categories.index');

Route::post('/unit-rental/categories', CreateUnitRentalCategory::class)
    ->name('api.unit-rental.categories.store');

Route::put('/unit-rental/categories/reorder', ReorderUnitRentalCategories::class)
    ->name('api.unit-rental.categories.reorder');

Route::put('/unit-rental/categories/{id}', UpdateUnitRentalCategory::class)
    ->name('api.unit-rental.categories.update')
    ->whereNumber('id');

Route::delete('/unit-rental/categories/{id}', DeleteUnitRentalCategory::class)
    ->name('api.unit-rental.categories.destroy')
    ->whereNumber('id');

Route::get('/unit-rental/calendars', ListUnitRentalCalendars::class)
    ->name('api.unit-rental.calendars.index');

Route::post('/unit-rental/calendars', CreateUnitRentalCalendar::class)
    ->name('api.unit-rental.calendars.store');

Route::get('/unit-rental/calendars/{id}', ShowUnitRentalCalendar::class)
    ->name('api.unit-rental.calendars.show')
    ->whereNumber('id');

Route::put('/unit-rental/calendars/{id}', UpdateUnitRentalCalendar::class)
    ->name('api.unit-rental.calendars.update')
    ->whereNumber('id');

Route::delete('/unit-rental/calendars/{id}', DeleteUnitRentalCalendar::class)
    ->name('api.unit-rental.calendars.destroy')
    ->whereNumber('id');

Route::get('/unit-rental/{uuid}', ShowUnitRental::class)
    ->name('api.unit-rental.show')
    ->whereUuid('uuid');

Route::put('/unit-rental/{uuid}', UpdateUnitRental::class)
    ->name('api.unit-rental.update')
    ->whereUuid('uuid');

Route::put('/unit-rental/{uuid}/active', ToggleUnitRentalActive::class)
    ->name('api.unit-rental.active')
    ->whereUuid('uuid');

Route::post('/unit-rental/{uuid}/clone', CloneUnitRental::class)
    ->name('api.unit-rental.clone')
    ->whereUuid('uuid');

Route::post('/unit-rental/{uuid}/images', UploadUnitRentalImage::class)
    ->name('api.unit-rental.images.store')
    ->whereUuid('uuid');

Route::put('/unit-rental/{uuid}/images/reorder', ReorderUnitRentalImages::class)
    ->name('api.unit-rental.images.reorder')
    ->whereUuid('uuid');

Route::delete('/unit-rental/{uuid}/images/{mediaId}', DeleteUnitRentalImage::class)
    ->name('api.unit-rental.images.destroy')
    ->whereUuid('uuid')
    ->whereNumber('mediaId');

Route::post('/unit-rental/{uuid}/editor-images', UploadUnitRentalEditorImage::class)
    ->name('api.unit-rental.editor-images.store')
    ->whereUuid('uuid');

Route::get('/digital-services', ListDigitalServices::class)
    ->name('api.digital-services.index');

Route::post('/digital-services', CreateDigitalService::class)
    ->name('api.digital-services.store');

Route::delete('/digital-services', DeleteDigitalServices::class)
    ->name('api.digital-services.destroy');

Route::get('/digital-services/settings', GetDigitalServiceSettings::class)
    ->name('api.digital-services.settings.show');

Route::put('/digital-services/settings', UpdateDigitalServiceSettings::class)
    ->name('api.digital-services.settings.update');

Route::get('/digital-services/categories', ListDigitalServiceCategories::class)
    ->name('api.digital-services.categories.index');

Route::post('/digital-services/categories', CreateDigitalServiceCategory::class)
    ->name('api.digital-services.categories.store');

Route::put('/digital-services/categories/reorder', ReorderDigitalServiceCategories::class)
    ->name('api.digital-services.categories.reorder');

Route::put('/digital-services/categories/{id}', UpdateDigitalServiceCategory::class)
    ->name('api.digital-services.categories.update')
    ->whereNumber('id');

Route::delete('/digital-services/categories/{id}', DeleteDigitalServiceCategory::class)
    ->name('api.digital-services.categories.destroy')
    ->whereNumber('id');

Route::get('/digital-services/{uuid}', ShowDigitalService::class)
    ->name('api.digital-services.show')
    ->whereUuid('uuid');

Route::put('/digital-services/{uuid}', UpdateDigitalService::class)
    ->name('api.digital-services.update')
    ->whereUuid('uuid');

Route::put('/digital-services/{uuid}/active', ToggleDigitalServiceActive::class)
    ->name('api.digital-services.active')
    ->whereUuid('uuid');

Route::post('/digital-services/{uuid}/clone', CloneDigitalService::class)
    ->name('api.digital-services.clone')
    ->whereUuid('uuid');

Route::post('/digital-services/{uuid}/images', UploadDigitalServiceImage::class)
    ->name('api.digital-services.images.store')
    ->whereUuid('uuid');

Route::put('/digital-services/{uuid}/images/reorder', ReorderDigitalServiceImages::class)
    ->name('api.digital-services.images.reorder')
    ->whereUuid('uuid');

Route::delete('/digital-services/{uuid}/images/{mediaId}', DeleteDigitalServiceImage::class)
    ->name('api.digital-services.images.destroy')
    ->whereUuid('uuid')
    ->whereNumber('mediaId');

Route::post('/digital-services/{uuid}/editor-images', UploadDigitalServiceEditorImage::class)
    ->name('api.digital-services.editor-images.store')
    ->whereUuid('uuid');

Route::get('/on-demand-services', ListOnDemandServices::class)
    ->name('api.on-demand-services.index');

Route::post('/on-demand-services', CreateOnDemandService::class)
    ->name('api.on-demand-services.store');

Route::delete('/on-demand-services', DeleteOnDemandServices::class)
    ->name('api.on-demand-services.destroy');

Route::get('/on-demand-services/settings', GetOnDemandServiceSettings::class)
    ->name('api.on-demand-services.settings.show');

Route::put('/on-demand-services/settings', UpdateOnDemandServiceSettings::class)
    ->name('api.on-demand-services.settings.update');

Route::get('/on-demand-services/{uuid}', ShowOnDemandService::class)
    ->name('api.on-demand-services.show')
    ->whereUuid('uuid');

Route::put('/on-demand-services/{uuid}', UpdateOnDemandService::class)
    ->name('api.on-demand-services.update')
    ->whereUuid('uuid');

Route::put('/on-demand-services/{uuid}/active', ToggleOnDemandServiceActive::class)
    ->name('api.on-demand-services.active')
    ->whereUuid('uuid');

Route::post('/on-demand-services/{uuid}/images', UploadOnDemandServiceImage::class)
    ->name('api.on-demand-services.images.store')
    ->whereUuid('uuid');

Route::put('/on-demand-services/{uuid}/images/reorder', ReorderOnDemandServiceImages::class)
    ->name('api.on-demand-services.images.reorder')
    ->whereUuid('uuid');

Route::delete('/on-demand-services/{uuid}/images/{mediaId}', DeleteOnDemandServiceImage::class)
    ->name('api.on-demand-services.images.destroy')
    ->whereUuid('uuid')
    ->whereNumber('mediaId');

Route::post('/on-demand-services/{uuid}/editor-images', UploadOnDemandServiceEditorImage::class)
    ->name('api.on-demand-services.editor-images.store')
    ->whereUuid('uuid');

Route::get('/digital-products', ListDigitalProducts::class)
    ->name('api.digital-products.index');

Route::post('/digital-products', CreateDigitalProduct::class)
    ->name('api.digital-products.store');

Route::delete('/digital-products', DeleteDigitalProducts::class)
    ->name('api.digital-products.destroy');

Route::get('/digital-products/settings', GetDigitalProductSettings::class)
    ->name('api.digital-products.settings.show');

Route::put('/digital-products/settings', UpdateDigitalProductSettings::class)
    ->name('api.digital-products.settings.update');

Route::get('/digital-products/categories', ListDigitalProductCategories::class)
    ->name('api.digital-products.categories.index');

Route::post('/digital-products/categories', CreateDigitalProductCategory::class)
    ->name('api.digital-products.categories.store');

Route::put('/digital-products/categories/reorder', ReorderDigitalProductCategories::class)
    ->name('api.digital-products.categories.reorder');

Route::put('/digital-products/categories/{id}', UpdateDigitalProductCategory::class)
    ->name('api.digital-products.categories.update')
    ->whereNumber('id');

Route::delete('/digital-products/categories/{id}', DeleteDigitalProductCategory::class)
    ->name('api.digital-products.categories.destroy')
    ->whereNumber('id');

Route::get('/digital-products/{uuid}', ShowDigitalProduct::class)
    ->name('api.digital-products.show')
    ->whereUuid('uuid');

Route::put('/digital-products/{uuid}', UpdateDigitalProduct::class)
    ->name('api.digital-products.update')
    ->whereUuid('uuid');

Route::put('/digital-products/{uuid}/active', ToggleDigitalProductActive::class)
    ->name('api.digital-products.active')
    ->whereUuid('uuid');

Route::post('/digital-products/{uuid}/images', UploadDigitalProductImage::class)
    ->name('api.digital-products.images.store')
    ->whereUuid('uuid');

Route::put('/digital-products/{uuid}/images/reorder', ReorderDigitalProductImages::class)
    ->name('api.digital-products.images.reorder')
    ->whereUuid('uuid');

Route::delete('/digital-products/{uuid}/images/{mediaId}', DeleteDigitalProductImage::class)
    ->name('api.digital-products.images.destroy')
    ->whereUuid('uuid')
    ->whereNumber('mediaId');

Route::post('/digital-products/{uuid}/editor-images', UploadDigitalProductEditorImage::class)
    ->name('api.digital-products.editor-images.store')
    ->whereUuid('uuid');

Route::post('/digital-products/{uuid}/downloads', UploadDigitalProductDownload::class)
    ->name('api.digital-products.downloads.store')
    ->whereUuid('uuid');

Route::put('/digital-products/{uuid}/downloads/reorder', ReorderDigitalProductDownloads::class)
    ->name('api.digital-products.downloads.reorder')
    ->whereUuid('uuid');

Route::delete('/digital-products/{uuid}/downloads/{mediaId}', DeleteDigitalProductDownload::class)
    ->name('api.digital-products.downloads.destroy')
    ->whereUuid('uuid')
    ->whereNumber('mediaId');

Route::get('/courses', ListCourses::class)
    ->name('api.courses.index');

Route::post('/courses', CreateCourse::class)
    ->name('api.courses.store');

Route::delete('/courses', DeleteCourses::class)
    ->name('api.courses.destroy');

Route::get('/courses/settings', GetCourseSettings::class)
    ->name('api.courses.settings.show');

Route::put('/courses/settings', UpdateCourseSettings::class)
    ->name('api.courses.settings.update');

Route::get('/courses/categories', ListCourseCategories::class)
    ->name('api.courses.categories.index');

Route::post('/courses/categories', CreateCourseCategory::class)
    ->name('api.courses.categories.store');

Route::put('/courses/categories/reorder', ReorderCourseCategories::class)
    ->name('api.courses.categories.reorder');

Route::put('/courses/categories/{id}', UpdateCourseCategory::class)
    ->name('api.courses.categories.update')
    ->whereNumber('id');

Route::delete('/courses/categories/{id}', DeleteCourseCategory::class)
    ->name('api.courses.categories.destroy')
    ->whereNumber('id');

Route::get('/courses/{uuid}', ShowCourse::class)
    ->name('api.courses.show')
    ->whereUuid('uuid');

Route::put('/courses/{uuid}', UpdateCourse::class)
    ->name('api.courses.update')
    ->whereUuid('uuid');

Route::post('/courses/{uuid}/cover-image', UploadCourseCoverImage::class)
    ->name('api.courses.cover-image.store')
    ->whereUuid('uuid');

Route::delete('/courses/{uuid}/cover-image/{mediaId}', DeleteCourseCoverImage::class)
    ->name('api.courses.cover-image.destroy')
    ->whereUuid('uuid')
    ->whereNumber('mediaId');

Route::post('/courses/{uuid}/editor-images', UploadCourseEditorImage::class)
    ->name('api.courses.editor-images.store')
    ->whereUuid('uuid');

Route::post('/courses/{uuid}/lesson-files', UploadCourseLessonFile::class)
    ->name('api.courses.lesson-files.store')
    ->whereUuid('uuid');

Route::delete('/courses/{uuid}/lesson-files/{mediaId}', DeleteCourseLessonFile::class)
    ->name('api.courses.lesson-files.destroy')
    ->whereUuid('uuid')
    ->whereNumber('mediaId');

Route::get('/newsletter', ListNewsletters::class)
    ->name('api.newsletter.index');

Route::post('/newsletter', CreateNewsletter::class)
    ->name('api.newsletter.store');

Route::delete('/newsletter', DeleteNewsletters::class)
    ->name('api.newsletter.destroy');

Route::get('/newsletter/settings', GetNewsletterSettings::class)
    ->name('api.newsletter.settings.show');

Route::put('/newsletter/settings', UpdateNewsletterSettings::class)
    ->name('api.newsletter.settings.update');

Route::get('/newsletter/{uuid}', ShowNewsletter::class)
    ->name('api.newsletter.show')
    ->whereUuid('uuid');

Route::put('/newsletter/{uuid}', UpdateNewsletter::class)
    ->name('api.newsletter.update')
    ->whereUuid('uuid');

Route::post('/newsletter/{uuid}/featured-image', UploadNewsletterFeaturedImage::class)
    ->name('api.newsletter.featured-image.store')
    ->whereUuid('uuid');

Route::delete('/newsletter/{uuid}/featured-image', DeleteNewsletterFeaturedImage::class)
    ->name('api.newsletter.featured-image.destroy')
    ->whereUuid('uuid');

Route::post('/newsletter/{uuid}/editor-images', UploadNewsletterEditorImage::class)
    ->name('api.newsletter.editor-images.store')
    ->whereUuid('uuid');

Route::get('/pages', ListPages::class)
    ->name('api.pages.index');

Route::post('/pages', CreatePage::class)
    ->name('api.pages.store');

Route::delete('/pages', DeletePages::class)
    ->name('api.pages.destroy');

Route::get('/pages/{uuid}', ShowPage::class)
    ->name('api.pages.show')
    ->whereUuid('uuid');

Route::put('/pages/{uuid}', UpdatePage::class)
    ->name('api.pages.update')
    ->whereUuid('uuid');

Route::put('/pages/{uuid}/active', TogglePageActive::class)
    ->name('api.pages.active')
    ->whereUuid('uuid');

Route::post('/pages/{uuid}/editor-images', UploadPageEditorImage::class)
    ->name('api.pages.editor-images.store')
    ->whereUuid('uuid');

Route::post('/pages/{uuid}/hero-image', UploadPageHeroImage::class)
    ->name('api.pages.hero-image.store')
    ->whereUuid('uuid');

Route::delete('/pages/{uuid}/hero-image', DeletePageHeroImage::class)
    ->name('api.pages.hero-image.destroy')
    ->whereUuid('uuid');

Route::post('/pages/{uuid}/brand-mark-image', UploadPageBrandMarkImage::class)
    ->name('api.pages.brand-mark-image.store')
    ->whereUuid('uuid');

Route::get('/pages/{uuid}/blocks', ListPageBlocks::class)
    ->name('api.pages.blocks.index')
    ->whereUuid('uuid');

Route::post('/pages/{uuid}/blocks', StandaloneCreatePageBlock::class)
    ->name('api.pages.blocks.store')
    ->whereUuid('uuid');

Route::put('/pages/{uuid}/blocks/reorder', StandaloneReorderPageBlocks::class)
    ->name('api.pages.blocks.reorder')
    ->whereUuid('uuid');

Route::get('/pages/{uuid}/blocks/{id}', StandaloneShowPageBlock::class)
    ->name('api.pages.blocks.show')
    ->whereUuid('uuid')
    ->whereNumber('id');

Route::match(['put', 'post'], '/pages/{uuid}/blocks/{id}', StandaloneUpdatePageBlock::class)
    ->name('api.pages.blocks.update')
    ->whereUuid('uuid')
    ->whereNumber('id');

Route::put('/pages/{uuid}/blocks/{id}/active', StandaloneTogglePageBlockActive::class)
    ->name('api.pages.blocks.active')
    ->whereUuid('uuid')
    ->whereNumber('id');

Route::delete('/pages/{uuid}/blocks/{id}', StandaloneDeletePageBlock::class)
    ->name('api.pages.blocks.destroy')
    ->whereUuid('uuid')
    ->whereNumber('id');

Route::get('/forms', ListForms::class)
    ->name('api.forms.index');

Route::post('/forms', CreateForm::class)
    ->name('api.forms.store');

Route::delete('/forms', DeleteForms::class)
    ->name('api.forms.destroy');

Route::get('/forms/{uuid}', ShowForm::class)
    ->name('api.forms.show')
    ->whereUuid('uuid');

Route::put('/forms/{uuid}', UpdateForm::class)
    ->name('api.forms.update')
    ->whereUuid('uuid');

Route::put('/forms/{uuid}/active', ToggleFormActive::class)
    ->name('api.forms.active')
    ->whereUuid('uuid');

Route::post('/forms/{uuid}/clone', CloneForm::class)
    ->name('api.forms.clone')
    ->whereUuid('uuid');
