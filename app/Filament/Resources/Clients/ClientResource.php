<?php

namespace App\Filament\Resources\Clients;

use App\Filament\Resources\Clients\Pages\ListClients;
use App\Filament\Resources\Clients\Pages\ViewClient;
use App\Filament\Resources\Tenants\TenantResource;
use App\Models\Client;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
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

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'العملاء';

    protected static ?string $modelLabel = 'عميل';

    protected static ?string $pluralModelLabel = 'العملاء';

    protected static string|UnitEnum|null $navigationGroup = 'إدارة المنصة';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('الاسم')
                    ->weight(FontWeight::SemiBold),
                TextEntry::make('email')
                    ->label('البريد الإلكتروني')
                    ->placeholder('—')
                    ->copyable(),
                TextEntry::make('phone')
                    ->label('رقم الجوال')
                    ->placeholder('—')
                    ->copyable(),
                IconEntry::make('active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedCheckCircle)
                    ->falseIcon(Heroicon::OutlinedXCircle)
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextEntry::make('tenant.name')
                    ->label('إقليم التسجيل')
                    ->placeholder('—')
                    ->url(fn (Client $record): ?string => $record->tenant
                        ? TenantResource::getUrl('view', ['record' => $record->tenant])
                        : null)
                    ->color('primary'),
                TextEntry::make('tenants_count')
                    ->label('عدد الأقاليم')
                    ->state(fn (Client $record): int => $record->tenants_count ?? $record->tenants()->count())
                    ->badge()
                    ->color('gray'),
                RepeatableEntry::make('tenants')
                    ->label('مسجّل في الأقاليم')
                    ->schema([
                        TextEntry::make('name')
                            ->label('الإقليم')
                            ->weight(FontWeight::Medium),
                        TextEntry::make('handle')
                            ->label('المعرّف')
                            ->badge(),
                        IconEntry::make('pivot.active')
                            ->label('نشط')
                            ->boolean(),
                    ])
                    ->columns(3)
                    ->placeholder('لا يوجد ارتباط بأقاليم أخرى'),
                TextEntry::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime('d M Y — h:i A'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium),
                TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->copyable(),
                TextColumn::make('phone')
                    ->label('رقم الجوال')
                    ->searchable()
                    ->placeholder('—'),
                IconColumn::make('active')
                    ->label('الحالة')
                    ->boolean(),
                TextColumn::make('tenants_count')
                    ->label('عدد الأقاليم')
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->alignCenter(),
                TextColumn::make('tenant.name')
                    ->label('إقليم التسجيل')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->url(fn (Client $record): ?string => $record->tenant
                        ? TenantResource::getUrl('view', ['record' => $record->tenant])
                        : null)
                    ->color('primary'),
                TextColumn::make('created_at')
                    ->label('التسجيل')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make()->label('عرض'),
            ])
            ->toolbarActions([])
            ->recordUrl(fn (Client $record): string => static::getUrl('view', ['record' => $record]));
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScope('tenantable')
            ->with(['tenant', 'tenants'])
            ->withCount('tenants');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClients::route('/'),
            'view' => ViewClient::route('/{record}'),
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
