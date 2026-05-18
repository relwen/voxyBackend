<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->bind('path.public', function () {
            realpath(base_path() . '/../public_html');
        });

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enregistrer le helper FileHelper
        if (!class_exists('FileHelper')) {
            require_once app_path('Helpers/FileHelper.php');
            require_once app_path('Helpers/IconHelper.php');
        }
    }
}
