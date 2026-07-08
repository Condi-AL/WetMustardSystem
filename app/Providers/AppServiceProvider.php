<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $appPath = trim((string) parse_url((string) config('app.url'), PHP_URL_PATH), '/');

        if ($appPath !== '') {
            $prefix = '/'.$appPath;

            // Ensure Livewire endpoints resolve correctly when the app is hosted in a subfolder.
            Livewire::setScriptRoute(fn ($handle) => Route::get($prefix.'/livewire/livewire.js', $handle));
            Livewire::setUpdateRoute(fn ($handle) => Route::post($prefix.'/livewire/update', $handle)->name('livewire.update'));
        }

        Gate::define('admin', fn ($user) => $user->hasRole('administrator'));
    }
}
