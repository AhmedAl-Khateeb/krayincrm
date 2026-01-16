<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\CallProviders\CallProviderInterface;
use App\Services\CallProviders\CiscoTelCallProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(CallProviderInterface::class, CiscoTelCallProvider::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
