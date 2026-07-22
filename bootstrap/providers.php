<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\SuperpassPanelProvider;
use App\Providers\UiServiceProvider;

return [
    AppServiceProvider::class,
    SuperpassPanelProvider::class,
    UiServiceProvider::class,
];
