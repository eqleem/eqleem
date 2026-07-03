<?php

namespace App\Providers;

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\ResolveTenantFromPath;
use App\Http\Middleware\SetTenantTheme;
use App\Support\BlockTypeRegistry;
use App\Support\BlockVariants;
use App\Support\ContentTypeRegistry;
use App\Support\PageTabRegistry;
use App\Support\TenantThemeOptions;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(BlockTypeRegistry::class);
        $this->app->singleton(BlockVariants::class);
        $this->app->singleton(ContentTypeRegistry::class);
        $this->app->singleton(PageTabRegistry::class);
        $this->app->singleton(TenantThemeOptions::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Livewire::addPersistentMiddleware([
            AdminMiddleware::class,
            ResolveTenantFromPath::class,
            SetTenantTheme::class,
        ]);
    }
}
