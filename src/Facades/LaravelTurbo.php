<?php

namespace Emaia\LaravelHotwireTurbo\Facades;

use Emaia\LaravelHotwireTurbo\Turbo;
use Illuminate\Support\Facades\Facade;

class LaravelTurbo extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Turbo::class;
    }
}
