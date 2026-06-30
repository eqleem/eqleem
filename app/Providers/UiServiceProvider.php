<?php

namespace App\Providers;
    
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class UiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Blade::anonymousComponentPath(resource_path('views/ui'), 'ui');
        view()->addNamespace('ui', resource_path('views/ui'));

        $this->bootTagCompiler();
    }

    public function bootTagCompiler()
    {
        $compiler = new UiTagCompiler(
            app('blade.compiler')->getClassComponentAliases(),
            app('blade.compiler')->getClassComponentNamespaces(),
            app('blade.compiler')
        );
   
        app('blade.compiler')->precompiler(function ($in) use ($compiler) {
            return $compiler->compile($in);
        });
    }

}
