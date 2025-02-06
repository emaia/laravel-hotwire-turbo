<?php

namespace Emaia\LaravelTurbo\Facades;

use Emaia\LaravelTurbo\StreamInterface;
use Illuminate\Support\Facades\Facade;

class LaravelTurbo extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Emaia\LaravelTurbo\LaravelTurbo::class;
    }
}
