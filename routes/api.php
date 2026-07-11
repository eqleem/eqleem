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
use App\API\Payments\ListPayments;
use App\API\Payments\ShowPayment;
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
