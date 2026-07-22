<?php

namespace App\Filament\Resources\Payments;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Payments\Pages\ListPayments;
use App\Filament\Resources\Payments\Pages\ViewPayment;
use App\Filament\Resources\Tenants\TenantResource;
use App\Models\Payment;
use App\Support\Money;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $recordTitleAttribute = 'uuid';

    protected static ?string $navigationLabel = 'المبيعات';

    protected static ?string $modelLabel = 'دفعة';

    protected static ?string $pluralModelLabel = 'المبيعات';

    protected static string|UnitEnum|null $navigationGroup = 'إدارة المنصة';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('tenant.name')
                    ->label('الإقليم')
                    ->placeholder('—')
                    ->url(fn (Payment $record): ?string => $record->tenant
                        ? TenantResource::getUrl('view', ['record' => $record->tenant])
                        : null)
                    ->color('primary')
                    ->weight(FontWeight::SemiBold),
                TextEntry::make('amount')
                    ->label('المبلغ')
                    ->formatStateUsing(fn (?int $state, Payment $record): string => Money::formatWithCurrency($state, $record->currency))
                    ->weight(FontWeight::Bold),
                TextEntry::make('status')
                    ->label('الحالة')
                    ->state(fn (Payment $record): string => $record->statusLabel())
                    ->badge()
                    ->color(fn (Payment $record): string => match ($record->statusBadgeColor()) {
                        'green' => 'success',
                        'red' => 'danger',
                        'yellow' => 'warning',
                        'purple' => 'info',
                        default => 'gray',
                    }),
                TextEntry::make('reason')
                    ->label('السبب')
                    ->state(fn (Payment $record): string => $record->reasonLabel()),
                TextEntry::make('gateway')
                    ->label('بوابة الدفع')
                    ->state(fn (Payment $record): string => $record->gatewayLabel()),
                TextEntry::make('payer')
                    ->label('الدافع')
                    ->state(fn (Payment $record): string => $record->payerName()),
                TextEntry::make('order.number')
                    ->label('رقم الطلب')
                    ->placeholder('—')
                    ->url(fn (Payment $record): ?string => $record->order
                        ? OrderResource::getUrl('view', ['record' => $record->order])
                        : null)
                    ->color('primary'),
                IconEntry::make('captured')
                    ->label('تم التحصيل')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->label('تاريخ الدفع')
                    ->dateTime('d M Y — h:i A'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tenant.name')
                    ->label('الإقليم')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->url(fn (Payment $record): ?string => $record->tenant
                        ? TenantResource::getUrl('view', ['record' => $record->tenant])
                        : null)
                    ->color('primary')
                    ->weight(FontWeight::Medium),
                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->formatStateUsing(fn (?int $state, Payment $record): string => Money::formatWithCurrency($state, $record->currency))
                    ->sortable()
                    ->weight(FontWeight::SemiBold),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->state(fn (Payment $record): string => $record->statusLabel())
                    ->badge()
                    ->color(fn (Payment $record): string => match ($record->statusBadgeColor()) {
                        'green' => 'success',
                        'red' => 'danger',
                        'yellow' => 'warning',
                        'purple' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('reason')
                    ->label('السبب')
                    ->state(fn (Payment $record): string => $record->reasonLabel())
                    ->toggleable(),
                TextColumn::make('gateway')
                    ->label('البوابة')
                    ->state(fn (Payment $record): string => $record->gatewayLabel())
                    ->toggleable(),
                TextColumn::make('payer')
                    ->label('الدافع')
                    ->state(fn (Payment $record): string => $record->payerName())
                    ->toggleable(),
                IconColumn::make('captured')
                    ->label('محصّل')
                    ->boolean(),
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
            ->recordUrl(fn (Payment $record): string => static::getUrl('view', ['record' => $record]));
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScope('tenant')
            ->with(['tenant', 'order', 'user', 'client']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayments::route('/'),
            'view' => ViewPayment::route('/{record}'),
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
