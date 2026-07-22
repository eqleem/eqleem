<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Tenants\TenantResource;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Models\User;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'المستخدمون';

    protected static ?string $modelLabel = 'مستخدم';

    protected static ?string $pluralModelLabel = 'المستخدمون';

    protected static string|UnitEnum|null $navigationGroup = 'إدارة المنصة';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                ImageEntry::make('image')
                    ->label('الصورة')
                    ->circular()
                    ->checkFileExistence(false),
                TextEntry::make('name')
                    ->label('الاسم')
                    ->weight(FontWeight::SemiBold),
                TextEntry::make('phone')
                    ->label('رقم الجوال')
                    ->placeholder('—')
                    ->copyable(),
                TextEntry::make('email')
                    ->label('البريد الإلكتروني')
                    ->copyable(),
                IconEntry::make('active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedCheckCircle)
                    ->falseIcon(Heroicon::OutlinedXCircle)
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextEntry::make('currentTenant.name')
                    ->label('الإقليم الحالي')
                    ->placeholder('—')
                    ->url(fn (User $record): ?string => $record->currentTenant
                        ? TenantResource::getUrl('view', ['record' => $record->currentTenant])
                        : null)
                    ->color('primary'),
                TextEntry::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime('d M Y — h:i A'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('الصورة')
                    ->circular()
                    ->imageSize(40)
                    ->checkFileExistence(false),
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium),
                TextColumn::make('phone')
                    ->label('رقم الجوال')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                ToggleColumn::make('active')
                    ->label('الحالة')
                    ->onColor('success')
                    ->offColor('danger'),
                TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('currentTenant.name')
                    ->label('الإقليم الحالي')
                    ->placeholder('—')
                    ->searchable()
                    ->url(fn (User $record): ?string => $record->currentTenant
                        ? TenantResource::getUrl('view', ['record' => $record->currentTenant])
                        : null)
                    ->color('primary')
                    ->weight(FontWeight::Medium)
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make()
                    ->label('عرض'),
            ])
            ->toolbarActions([])
            ->recordUrl(fn (User $record): string => static::getUrl('view', ['record' => $record]));
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('currentTenant');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'view' => ViewUser::route('/{record}'),
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
