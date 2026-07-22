<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Clients\ClientResource;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Filament\Resources\Orders\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\Tenants\TenantResource;
use App\Models\Order;
use App\Support\Money;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static ?string $recordTitleAttribute = 'number';

    protected static ?string $navigationLabel = 'الطلبات';

    protected static ?string $modelLabel = 'طلب';

    protected static ?string $pluralModelLabel = 'الطلبات';

    protected static string|UnitEnum|null $navigationGroup = 'إدارة المنصة';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('number')
                    ->label('رقم الطلب')
                    ->weight(FontWeight::Bold)
                    ->copyable(),
                TextEntry::make('tenant.name')
                    ->label('الإقليم')
                    ->placeholder('—')
                    ->url(fn (Order $record): ?string => $record->tenant
                        ? TenantResource::getUrl('view', ['record' => $record->tenant])
                        : null)
                    ->color('primary'),
                TextEntry::make('client.name')
                    ->label('العميل')
                    ->placeholder(fn (Order $record): string => Order::walkingClientLabel())
                    ->url(fn (Order $record): ?string => $record->client
                        ? ClientResource::getUrl('view', ['record' => $record->client])
                        : null)
                    ->color('primary'),
                TextEntry::make('status')
                    ->label('حالة الطلب')
                    ->state(fn (Order $record): string => $record->statusLabel())
                    ->badge()
                    ->color(fn (Order $record): string => match ($record->statusBadgeColor()) {
                        'green' => 'success',
                        'red' => 'danger',
                        'yellow' => 'warning',
                        'blue', 'teal', 'purple' => 'info',
                        default => 'gray',
                    }),
                TextEntry::make('payment_status')
                    ->label('حالة الدفع')
                    ->state(fn (Order $record): string => $record->paymentStatusLabel())
                    ->badge()
                    ->color(fn (Order $record): string => match ($record->paymentStatusBadgeColor()) {
                        'green' => 'success',
                        'red' => 'danger',
                        'yellow' => 'warning',
                        'purple' => 'info',
                        default => 'gray',
                    }),
                TextEntry::make('grand_total')
                    ->label('الإجمالي')
                    ->formatStateUsing(fn (?int $state, Order $record): string => Money::formatWithCurrency($state, $record->currency_code))
                    ->weight(FontWeight::Bold),
                TextEntry::make('channel')
                    ->label('القناة')
                    ->state(fn (Order $record): string => $record->channelLabel()),
                TextEntry::make('created_at')
                    ->label('تاريخ الطلب')
                    ->dateTime('d M Y — h:i A'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('رقم الطلب')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->copyable(),
                TextColumn::make('tenant.name')
                    ->label('الإقليم')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->url(fn (Order $record): ?string => $record->tenant
                        ? TenantResource::getUrl('view', ['record' => $record->tenant])
                        : null)
                    ->color('primary'),
                TextColumn::make('client.name')
                    ->label('العميل')
                    ->searchable()
                    ->sortable()
                    ->placeholder(fn (Order $record): string => Order::walkingClientLabel()),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->state(fn (Order $record): string => $record->statusLabel())
                    ->badge()
                    ->color(fn (Order $record): string => match ($record->statusBadgeColor()) {
                        'green' => 'success',
                        'red' => 'danger',
                        'yellow' => 'warning',
                        'blue', 'teal', 'purple' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('payment_status')
                    ->label('الدفع')
                    ->state(fn (Order $record): string => $record->paymentStatusLabel())
                    ->badge()
                    ->color(fn (Order $record): string => match ($record->paymentStatusBadgeColor()) {
                        'green' => 'success',
                        'red' => 'danger',
                        'yellow' => 'warning',
                        'purple' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('grand_total')
                    ->label('الإجمالي')
                    ->formatStateUsing(fn (?int $state, Order $record): string => Money::formatWithCurrency($state, $record->currency_code))
                    ->sortable()
                    ->weight(FontWeight::SemiBold),
                TextColumn::make('channel')
                    ->label('القناة')
                    ->state(fn (Order $record): string => $record->channelLabel())
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make()->label('عرض'),
            ])
            ->toolbarActions([])
            ->recordUrl(fn (Order $record): string => static::getUrl('view', ['record' => $record]));
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScope('tenant')
            ->with(['tenant', 'client']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'view' => ViewOrder::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
