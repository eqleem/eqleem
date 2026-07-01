<?php

namespace App\Providers;

use App\Http\Middleware\AdminMiddleware;
use App\Support\BlockPositionRegistry;
use App\Support\BlockTypeRegistry;
use App\Support\ContentTypeRegistry;
use App\Support\PageTabRegistry;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(BlockPositionRegistry::class);
        $this->app->singleton(BlockTypeRegistry::class);
        $this->app->singleton(ContentTypeRegistry::class);
        $this->app->singleton(PageTabRegistry::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Livewire::addPersistentMiddleware([
            AdminMiddleware::class,
        ]);
    }
}
