<?php

namespace Emaia\LaravelTurbo\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Emaia\LaravelTurbo\LaravelTurbo
 */
class LaravelTurbo extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Emaia\LaravelTurbo\LaravelTurbo::class;
    }
}
