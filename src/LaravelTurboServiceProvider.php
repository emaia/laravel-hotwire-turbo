<?php

namespace Emaia\LaravelTurbo;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Emaia\LaravelTurbo\Commands\LaravelTurboCommand;

class LaravelTurboServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-turbo');
            // ->hasConfigFile()
            // ->hasViews()
            // ->hasMigration('create_laravel_turbo_table')
            // ->hasCommand(LaravelTurboCommand::class);
    }
}
