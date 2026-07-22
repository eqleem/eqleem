<?php

namespace App\Filament\Resources\Plans;

use App\Filament\Resources\Plans\Pages\CreatePlan;
use App\Filament\Resources\Plans\Pages\EditPlan;
use App\Filament\Resources\Plans\Pages\ListPlans;
use App\Models\Plan;
use App\Support\Money;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use LucasDotVin\Soulbscription\Enums\PeriodicityType;
use UnitEnum;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'label';

    protected static ?string $navigationLabel = 'الخطط';

    protected static ?string $modelLabel = 'خطة';

    protected static ?string $pluralModelLabel = 'الخطط';

    protected static string|UnitEnum|null $navigationGroup = 'إدارة المنصة';

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الخطة')
                    ->columns(2)
                    ->schema([
                        TextInput::make('label')
                            ->label('الاسم الظاهر')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, callable $set, callable $get): void {
                                if (filled($get('name')) && filled($get('slug'))) {
                                    return;
                                }

                                $base = Str::slug((string) $state) ?: 'plan';

                                if (blank($get('name'))) {
                                    $set('name', $base);
                                }

                                if (blank($get('slug'))) {
                                    $set('slug', $base);
                                }
                            }),
                        TextInput::make('name')
                            ->label('الاسم الداخلي')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('slug')
                            ->label('المعرّف')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->alphaDash(),
                        Textarea::make('meta.description')
                            ->label('الوصف')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Section::make('التسعير والدورة')
                    ->columns(2)
                    ->schema([
                        TextInput::make('price')
                            ->label('السعر')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->suffix(Money::symbolFor())
                            ->formatStateUsing(fn (?int $state): ?string => $state === null ? null : number_format(Money::fromMinor($state), 2, '.', ''))
                            ->dehydrateStateUsing(fn ($state): int => Money::toMinor($state)),
                        TextInput::make('grace_days')
                            ->label('أيام السماح')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                        TextInput::make('periodicity')
                            ->label('مدة الدورة')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->nullable()
                            ->helperText('اتركه فارغاً للخطط بدون دورة (مثل المجانية).'),
                        Select::make('periodicity_type')
                            ->label('نوع الدورة')
                            ->options([
                                PeriodicityType::Day => 'يومي',
                                PeriodicityType::Week => 'أسبوعي',
                                PeriodicityType::Month => 'شهري',
                                PeriodicityType::Year => 'سنوي',
                            ])
                            ->nullable(),
                    ]),
                Section::make('الحالة')
                    ->columns(3)
                    ->schema([
                        Toggle::make('active')
                            ->label('مفعّلة')
                            ->default(true)
                            ->required(),
                        Toggle::make('is_featured')
                            ->label('مميزة')
                            ->default(false),
                        Toggle::make('is_system')
                            ->label('خطة نظام')
                            ->default(true)
                            ->helperText('خطط المنصة العامة تظهر في صفحة الاشتراكات.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->description(fn (Plan $record): string => $record->slug),
                TextColumn::make('price')
                    ->label('السعر')
                    ->formatStateUsing(fn (?int $state): string => $state
                        ? Money::formatWithCurrency($state)
                        : 'مجانية')
                    ->sortable()
                    ->weight(FontWeight::SemiBold),
                TextColumn::make('billing')
                    ->label('الدورة')
                    ->state(fn (Plan $record): string => $record->billingLabel() ?: '—'),
                ToggleColumn::make('active')
                    ->label('مفعّلة')
                    ->onColor('success')
                    ->offColor('danger'),
                IconColumn::make('is_featured')
                    ->label('مميزة')
                    ->boolean(),
                IconColumn::make('is_system')
                    ->label('نظام')
                    ->boolean()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('price')
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()->label('تعديل'),
                DeleteAction::make()->label('حذف'),
                RestoreAction::make()->label('استعادة'),
                ForceDeleteAction::make()->label('حذف نهائي'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(fn (Plan $record): string => static::getUrl('edit', ['record' => $record]));
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPlans::route('/'),
            'create' => CreatePlan::route('/create'),
            'edit' => EditPlan::route('/{record}/edit'),
        ];
    }
}
