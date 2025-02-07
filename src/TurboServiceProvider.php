<?php

namespace Emaia\LaravelHotwireTurbo;

use Emaia\LaravelHotwireTurbo\Response as TurboResponse;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Illuminate\View\Compilers\BladeCompiler;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class TurboServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-turbo')
            ->hasViews();
    }

    public function packageBooted(): void
    {

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'turbo');

        $this->callAfterResolving('blade.compiler', function (BladeCompiler $blade) {
            $blade->anonymousComponentPath(__DIR__.'/../resources/views/components', 'turbo');
        });

        Request::macro('wantsTurboStream', function () {
            if (Str::contains(request()->header('Accept', ''), 'text/vnd.turbo-stream')) {
                return true;
            }

            return false;
        });

        Request::macro('wasFromTurboFrame', function (?string $frame = null): bool {
            if (! $frame) {
                return $this->hasHeader('Turbo-Frame');
            }

            return $this->header('Turbo-Frame', null) === $frame;
        });

        Response::macro('turboStream', function (StreamInterface $content, $status = 200, array $headers = []) {
            return new TurboResponse($content, $status, $headers);
        });
    }
}
