<?php

namespace App\Providers;

use App\Helpers\SidebarMenuHelper;
use Fruitcake\LaravelDebugbar\Facades\Debugbar;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $loader = AliasLoader::getInstance();
        if (class_exists(Debugbar::class)) {
            $loader->alias('Debugbar', Debugbar::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(function (Login $event) {
            try {
                activity('auth')->causedBy($event->user)->event('login')->withProperties([
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                    ->log('User logged in');
            } catch (\Throwable $e) {
                // Prevent login failure
            }
        });

        Event::listen(function (Logout $event) {
            try {
                activity('auth')->causedBy($event->user)->event('logout')->withProperties([
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                    ->log('User logged out');
            } catch (\Throwable $e) {
                // Prevent logout failure
            }
        });

    }
}

// getSidebarMenu helper function
if (! function_exists('getSidebarMenu')) {
    function getSidebarMenu()
    {
        try {
            return SidebarMenuHelper::getSidebarMenu();
        } catch (\Throwable $e) {
            return [];
        }
    }
}


