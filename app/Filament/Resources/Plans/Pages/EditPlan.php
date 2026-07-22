<?php

namespace App\Filament\Resources\Plans\Pages;

use App\Filament\Resources\Plans\PlanResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPlan extends EditRecord
{
    protected static string $resource = PlanResource::class;

    protected static ?string $title = 'تعديل الخطة';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('حذف'),
            RestoreAction::make()->label('استعادة'),
            ForceDeleteAction::make()->label('حذف نهائي'),
        ];
    }
}
