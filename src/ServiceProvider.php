<?php

namespace acidjazz\metapi;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views/', 'metapi');
    }
}
