<?php

namespace App\Providers;

use App\Utility\UtilityClass;
use Illuminate\Support\ServiceProvider;

class UtitlityServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind('utilityclass', function () {
            return new UtilityClass;
        });
    }
}
