<?php

namespace Emaia\LaravelHotwireTurbo\Http\Middleware;

use Closure;
use Emaia\LaravelHotwireTurbo\Http\TurboRequestVariation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TurboMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (! $response instanceof RedirectResponse) {
            return $response;
        }

        // The redirect status depends on Turbo headers, including their absence.
        TurboRequestVariation::mark($request);

        if (! $this->isTurboVisit($request)) {
            return $response;
        }

        return $response->setStatusCode(303);
    }

    private function isTurboVisit(Request $request): bool
    {
        return Str::contains($request->header('Accept', ''), 'text/vnd.turbo-stream')
            || $request->hasHeader('Turbo-Frame');
    }
}
