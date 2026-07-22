<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Models\OrderItem;
use App\Support\Money;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'عناصر الطلب';

    protected static ?string $modelLabel = 'عنصر';

    protected static ?string $pluralModelLabel = 'عناصر الطلب';

    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('type')
                    ->label('النوع')
                    ->state(fn (OrderItem $record): string => $record->typeLabel())
                    ->badge()
                    ->color('gray'),
                TextColumn::make('qty')
                    ->label('الكمية')
                    ->alignCenter(),
                TextColumn::make('unit_price')
                    ->label('سعر الوحدة')
                    ->formatStateUsing(fn (?int $state): string => Money::format($state)),
                TextColumn::make('discount_total')
                    ->label('الخصم')
                    ->formatStateUsing(fn (?int $state): string => Money::format($state))
                    ->toggleable(),
                TextColumn::make('line_total')
                    ->label('الإجمالي')
                    ->formatStateUsing(fn (?int $state): string => Money::format($state))
                    ->weight(FontWeight::SemiBold),
            ])
            ->defaultSort('id')
            ->paginated(false)
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
