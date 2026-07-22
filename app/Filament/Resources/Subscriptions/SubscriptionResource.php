<?php

namespace App\Filament\Resources\Subscriptions;

use App\Filament\Resources\Subscriptions\Pages\ListSubscriptions;
use App\Filament\Resources\Subscriptions\Pages\ViewSubscription;
use App\Filament\Resources\Tenants\TenantResource;
use App\Models\Tenant;
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
use LucasDotVin\Soulbscription\Models\Scopes\ExpiringWithGraceDaysScope;
use LucasDotVin\Soulbscription\Models\Scopes\StartingScope;
use LucasDotVin\Soulbscription\Models\Scopes\SuppressingScope;
use LucasDotVin\Soulbscription\Models\Subscription;
use UnitEnum;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $navigationLabel = 'المشتركين';

    protected static ?string $modelLabel = 'اشتراك';

    protected static ?string $pluralModelLabel = 'المشتركين';

    protected static string|UnitEnum|null $navigationGroup = 'إدارة المنصة';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('subscriber.name')
                    ->label('الإقليم')
                    ->placeholder('—')
                    ->url(fn (Subscription $record): ?string => $record->subscriber instanceof Tenant
                        ? TenantResource::getUrl('view', ['record' => $record->subscriber])
                        : null)
                    ->color('primary')
                    ->weight(FontWeight::SemiBold),
                TextEntry::make('plan.name')
                    ->label('الباقة')
                    ->badge()
                    ->placeholder('—'),
                TextEntry::make('status')
                    ->label('الحالة')
                    ->state(fn (Subscription $record): string => static::statusLabel($record))
                    ->badge()
                    ->color(fn (Subscription $record): string => static::statusColor($record)),
                TextEntry::make('started_at')
                    ->label('تاريخ البدء')
                    ->date('d M Y')
                    ->placeholder('—'),
                TextEntry::make('expired_at')
                    ->label('تاريخ الانتهاء')
                    ->dateTime('d M Y — h:i A')
                    ->placeholder('—'),
                TextEntry::make('grace_days_ended_at')
                    ->label('انتهاء فترة السماح')
                    ->dateTime('d M Y — h:i A')
                    ->placeholder('—'),
                TextEntry::make('canceled_at')
                    ->label('تاريخ الإلغاء')
                    ->dateTime('d M Y — h:i A')
                    ->placeholder('—'),
                TextEntry::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d M Y — h:i A'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subscriber.name')
                    ->label('الإقليم')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->url(fn (Subscription $record): ?string => $record->subscriber instanceof Tenant
                        ? TenantResource::getUrl('view', ['record' => $record->subscriber])
                        : null)
                    ->color('primary')
                    ->weight(FontWeight::Medium),
                TextColumn::make('plan.name')
                    ->label('الباقة')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->state(fn (Subscription $record): string => static::statusLabel($record))
                    ->badge()
                    ->color(fn (Subscription $record): string => static::statusColor($record)),
                TextColumn::make('started_at')
                    ->label('البدء')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('expired_at')
                    ->label('الانتهاء')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('canceled_at')
                    ->label('الإلغاء')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('الإنشاء')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make()->label('عرض'),
            ])
            ->toolbarActions([])
            ->recordUrl(fn (Subscription $record): string => static::getUrl('view', ['record' => $record]));
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                ExpiringWithGraceDaysScope::class,
                StartingScope::class,
                SuppressingScope::class,
            ])
            ->where('plan_id', '!=', 1)
            ->with(['plan', 'subscriber']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubscriptions::route('/'),
            'view' => ViewSubscription::route('/{record}'),
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

    public static function statusLabel(Subscription $record): string
    {
        if (filled($record->canceled_at)) {
            return 'ملغي';
        }

        if (filled($record->suppressed_at)) {
            return 'موقوف';
        }

        if ($record->expired_at && $record->expired_at->isPast()) {
            return 'منتهي';
        }

        if ($record->started_at && $record->started_at->isFuture()) {
            return 'مجدول';
        }

        return 'نشط';
    }

    public static function statusColor(Subscription $record): string
    {
        return match (static::statusLabel($record)) {
            'نشط' => 'success',
            'مجدول' => 'info',
            'منتهي' => 'warning',
            'ملغي', 'موقوف' => 'danger',
            default => 'gray',
        };
    }
}
