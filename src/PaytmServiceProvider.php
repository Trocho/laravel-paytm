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
        // Publish Configuration File to base Path.
        $this->publishes([
            __DIR__ . '/config/config.php' => base_path('config/paytm.php'),
            __DIR__ . '/view/paytm-payment/redirect.php' => base_path('resources/views/vendor/paytm-payment/redirect.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

        $this->app->bind('paytm', function () {
            return new Paytm;
        });
    }

}
