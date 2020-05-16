<?php

namespace acidjazz\metapi;

class Facade extends \Illuminate\Support\Facades\Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return MetApiController::class;
    }
}
