<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Clients\ClientResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Payments\PaymentResource;
use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Filament\Resources\Tenants\TenantResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\Client;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use App\Support\FilamentStatBuilder;
use App\Support\Money;
use Filament\Widgets\StatsOverviewWidget;
use LucasDotVin\Soulbscription\Models\Scopes\ExpiringWithGraceDaysScope;
use LucasDotVin\Soulbscription\Models\Scopes\StartingScope;
use LucasDotVin\Soulbscription\Models\Scopes\SuppressingScope;
use LucasDotVin\Soulbscription\Models\Subscription;

class StatsOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected int $periodDays = 30;

    protected function getStats(): array
    {
        return [
            FilamentStatBuilder::count(
                label: 'إجمالي المستخدمين',
                query: User::query(),
                periodDays: $this->periodDays,
                url: UserResource::getUrl('index'),
            ),
            FilamentStatBuilder::count(
                label: 'الأقاليم',
                query: Tenant::query(),
                periodDays: $this->periodDays,
                url: TenantResource::getUrl('index'),
            ),
            FilamentStatBuilder::count(
                label: 'العملاء',
                query: Client::query()->withoutGlobalScope('tenantable'),
                periodDays: $this->periodDays,
                url: ClientResource::getUrl('index'),
            ),
            FilamentStatBuilder::count(
                label: 'المشتركين',
                query: Subscription::query()->withoutGlobalScopes([
                    ExpiringWithGraceDaysScope::class,
                    StartingScope::class,
                    SuppressingScope::class,
                ])->where('plan_id', '!=', 1),
                periodDays: $this->periodDays,
                url: SubscriptionResource::getUrl('index'),
            ),
            FilamentStatBuilder::sum(
                label: 'إجمالي المبيعات',
                query: Payment::query()->withoutGlobalScope('tenant'),
                column: 'amount',
                formatValue: fn (int $total): string => Money::formatWithCurrency($total),
                periodDays: $this->periodDays,
                url: PaymentResource::getUrl('index'),
            ),
            FilamentStatBuilder::count(
                label: 'الطلبات',
                query: Order::query()->withoutGlobalScope('tenant'),
                periodDays: $this->periodDays,
                url: OrderResource::getUrl('index'),
            ),
        ];
    }
}
