<?php

use App\API\Clients\CreateClient;
use App\API\Clients\ListClientInvoices;
use App\API\Clients\ListClientOrders;
use App\API\Clients\ListClients;
use App\API\Clients\ShowClient;
use App\API\Dashboard\AddWelcomeSocialLink;
use App\API\Dashboard\GetDashboardChart;
use App\API\Dashboard\GetDashboardContext;
use App\API\Dashboard\GetDashboardStat;
use App\API\Dashboard\GetDashboardStats;
use App\API\Dashboard\GetWelcomeWidget;
use App\API\Dashboard\UpdateWelcomeBasicInfo;
use App\API\Dashboard\UpdateWelcomeContact;
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
use App\API\DigitalServices\UpdateDigitalService;
use App\API\DigitalServices\UpdateDigitalServiceCategory;
use App\API\DigitalServices\UpdateDigitalServiceSettings;
use App\API\DigitalServices\UploadDigitalServiceEditorImage;
use App\API\DigitalServices\UploadDigitalServiceImage;
use App\API\FormSubmissions\ListFormSubmissions;
use App\API\FormSubmissions\ShowFormSubmission;
use App\API\Invoices\ListInvoices;
use App\API\Invoices\ShowInvoice;
use App\API\Orders\CreateOrder;
use App\API\Orders\CreateOrderContent;
use App\API\Orders\ListOrders;
use App\API\Orders\RecordOrderPayment;
use App\API\Orders\SearchOrderContent;
use App\API\Orders\ShowOrder;
use App\API\Page\AddPageHeaderSocialLink;
use App\API\Page\CreatePageBlock;
use App\API\Page\DeletePageBlock;
use App\API\Page\DeletePageBlockLink;
use App\API\Page\DeletePageHeaderSocialLink;
use App\API\Page\GetPageDesign;
use App\API\Page\GetPageStructure;
use App\API\Page\ReorderPageBlockLinks;
use App\API\Page\ReorderPageBlocks;
use App\API\Page\ReorderPageHeaderSocialLinks;
use App\API\Page\SavePageThemeOptions;
use App\API\Page\SearchPageLinkContent;
use App\API\Page\SetDefaultPageTheme;
use App\API\Page\ShowPageBlock;
use App\API\Page\TogglePageBlockActive;
use App\API\Page\UpdatePageBlock;
use App\API\Page\UpsertPageBlockLink;
use App\API\Payments\ListPayments;
use App\API\Payments\ShowPayment;
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
use App\API\Portfolio\UpdatePortfolioCategory;
use App\API\Portfolio\UpdatePortfolioProject;
use App\API\Portfolio\UpdatePortfolioSettings;
use App\API\Portfolio\UploadPortfolioEditorImage;
use App\API\Portfolio\UploadPortfolioImage;
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
use App\API\Store\UpdateStoreCategory;
use App\API\Store\UpdateStoreProduct;
use App\API\Store\UpdateStoreSettings;
use App\API\Store\UploadStoreEditorImage;
use App\API\Store\UploadStoreImage;
use App\API\User\UpdateAccountProfile;
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

Route::get('/dashboard/welcome', GetWelcomeWidget::class)
    ->name('api.dashboard.welcome');

Route::post('/dashboard/welcome/basic-info', UpdateWelcomeBasicInfo::class)
    ->name('api.dashboard.welcome.basic-info');

Route::put('/dashboard/welcome/contact', UpdateWelcomeContact::class)
    ->name('api.dashboard.welcome.contact');

Route::post('/dashboard/welcome/social', AddWelcomeSocialLink::class)
    ->name('api.dashboard.welcome.social');

Route::get('/orders', ListOrders::class)
    ->name('api.orders.index');

Route::post('/orders', CreateOrder::class)
    ->name('api.orders.store');

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

Route::put('/settings/general-info/basic', UpdateGeneralInfoBasic::class)
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

Route::get('/page/design', GetPageDesign::class)
    ->name('api.page.design');

Route::put('/page/design/theme', SetDefaultPageTheme::class)
    ->name('api.page.design.theme');

Route::match(['put', 'post'], '/page/design/options', SavePageThemeOptions::class)
    ->name('api.page.design.options');

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

Route::put('/page/blocks/{id}/links/{linkId}', UpsertPageBlockLink::class)
    ->name('api.page.blocks.links.update')
    ->whereNumber('id')
    ->whereNumber('linkId');

Route::delete('/page/blocks/{id}/links/{linkId}', DeletePageBlockLink::class)
    ->name('api.page.blocks.links.destroy')
    ->whereNumber('id')
    ->whereNumber('linkId');

Route::get('/page/link-content', SearchPageLinkContent::class)
    ->name('api.page.link-content');

Route::post('/page/header/social', AddPageHeaderSocialLink::class)
    ->name('api.page.header.social.store');

Route::put('/page/header/social/reorder', ReorderPageHeaderSocialLinks::class)
    ->name('api.page.header.social.reorder');

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
