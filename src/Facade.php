<?php

namespace acidjazz\metapi;

class Facade extends \Illuminate\Support\Facades\Facade
{
  protected static function getFacadeAccessor()
  {
    return MetApiController::class;
  }
}
