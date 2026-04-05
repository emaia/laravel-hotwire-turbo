<?php

namespace Emaia\LaravelHotwireTurbo;

use Closure;
use Emaia\LaravelHotwireTurbo\Response as TurboResponse;
use Emaia\LaravelHotwireTurbo\Testing\AssertableTurboStream;
use Emaia\LaravelHotwireTurbo\Testing\ConvertTestResponseToTurboStreamCollection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Illuminate\View\Compilers\BladeCompiler;
use PHPUnit\Framework\Assert;
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

        Request::macro('wantsTurboStream', function (): bool {
            return Str::contains(request()->header('Accept', ''), 'text/vnd.turbo-stream');
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

        Blade::directive('turboNocache', function () {
            return '<meta name="turbo-cache-control" content="no-cache">';
        });

        Blade::directive('turboNoPreview', function () {
            return '<meta name="turbo-cache-control" content="no-preview">';
        });

        Blade::directive('turboRefreshMethod', function (string $method) {
            return "<?php echo '<meta name=\"turbo-refresh-method\" content=\"'.e({$method}).'\">' ?>";
        });

        Blade::directive('turboRefreshScroll', function (string $scroll) {
            return "<?php echo '<meta name=\"turbo-refresh-scroll\" content=\"'.e({$scroll}).'\">' ?>";
        });

        Blade::directive('domid', function (string $expression) {
            return "<?php echo e(dom_id({$expression})); ?>";
        });

        Blade::directive('domclass', function (string $expression) {
            return "<?php echo e(dom_class({$expression})); ?>";
        });

        $this->registerTestingMacros();
    }

    private function registerTestingMacros(): void
    {
        if (! class_exists(TestResponse::class)) {
            return;
        }

        TestResponse::macro('assertTurboStream', function (?Closure $callback = null): TestResponse {
            /** @var TestResponse $this */
            $this->assertHeader('Content-Type', 'text/vnd.turbo-stream.html; charset=UTF-8');

            $streams = ConvertTestResponseToTurboStreamCollection::convert($this);
            $assertable = new AssertableTurboStream($streams);

            if ($callback) {
                $callback($assertable);
            }

            return $this;
        });

        TestResponse::macro('assertNotTurboStream', function (): TestResponse {
            /** @var TestResponse $this */
            $contentType = $this->headers->get('Content-Type', '');

            Assert::assertStringNotContainsString(
                'text/vnd.turbo-stream.html',
                $contentType,
                'Response Content-Type should not be a Turbo Stream.',
            );

            return $this;
        });
    }
}
