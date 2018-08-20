<?php

namespace acidjazz\metapi;

use Illuminate\Support\ServiceProvider;

class MetapiServiceProvider extends ServiceProvider
{

  public function boot()
  {
    $this->loadViewsFrom(__DIR__.'/resources/views/', 'metapi');
  }

}
