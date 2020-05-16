<?php

namespace acidjazz\metapi;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->app->bind(MetApi::class);
    }
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views/', 'metapi');
    }
}
