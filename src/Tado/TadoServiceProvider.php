<?php

namespace Tado;

class TadoServiceProvider extends \Illuminate\Support\ServiceProvider
{

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes(
            [
            __DIR__.'/../../config/tado.php' => config_path('tado.php'),
        ]
        );

        if ($this->app->runningInConsole()) {
            $this->commands(
                [

            ]
            );
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/tado.php',
            'tado'
        );

        $this->app->singleton(Client::class, function ($app) {
            return new Client(config('tado.username'), config('tado.password'));
        }
        );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            Client::class
        ];
    }
}
