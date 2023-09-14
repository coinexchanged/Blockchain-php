<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use GuzzleHttp\Client;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        defined('DECIMAL_SCALE') || define('DECIMAL_SCALE', 8);
        bcscale(DECIMAL_SCALE);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Client::class, function ($app) {
            return new Client();
        });
        $this->app->singleton('LbxChainServer', function ($app) {
            $api_url = config('app.wallet_api');
            return new Client(['base_uri' => $api_url]);
        });
    }
}
