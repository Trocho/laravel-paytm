<?php namespace Trocho\LaravelPaytm;

use Illuminate\Support\ServiceProvider;

class PaytmServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('trocho/laravel-paytm', 'laravel-paytm', __DIR__);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(Paytm::class);
    }
}
