<?php

namespace HieuLe\LaravelMenu;

use HieuLe\Active\ActiveServiceProvider;
use Illuminate\Support\ServiceProvider;

class LaravelMenuServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->loadViewsFrom(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'views', MenuManager::PLUGIN_NAME);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('menu.manager', function ($app) {
            return new MenuManager($app['view'], $app['url']);
        });

        $this->app->register(ActiveServiceProvider::class);
    }
}