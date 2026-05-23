<?php

namespace Emaia\LaravelHotwireTurbo\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class TurboFormRequest extends FormRequest
{
    protected function failedValidation(Validator $validator): void
    {
        if (request()->wasFromTurboFrame()) {
            $this->redirect = $this->resolveFrameSourceUrl();
        }

        parent::failedValidation($validator);
    }

    private function resolveFrameSourceUrl(): string
    {
        return $this->sanitizeRedirectUrl(
            $this->input('_turbo_frame_src')
                ?: $this->header('X-Turbo-Frame-Src')
                ?: $this->getFrameSourceFallback()
        );
    }

    private function getFrameSourceFallback(): string
    {
        $url = session('_previous.url');

        if ($url) {
            return $url;
        }

        throw new \RuntimeException(
            'TurboFormRequest: unable to determine frame source URL. '.
            'Add @turboFrameSrc directive to your form inside the Turbo Frame.'
        );
    }

    private function sanitizeRedirectUrl(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST);

        // Relative or unparseable URLs are inherently same-origin.
        if ($host === null || $host === false) {
            return $url;
        }

        if (in_array($host, $this->trustedRedirectHosts(), true)) {
            return $url;
        }

        Log::warning('TurboFormRequest rejected redirect URL; falling back to "/".', [
            'url' => $url,
            'host' => $host,
            'trusted_hosts' => $this->trustedRedirectHosts(),
        ]);

        return url('/');
    }

    private function trustedRedirectHosts(): array
    {
        $configured = array_map(
            fn ($entry) => is_string($entry) ? (parse_url($entry, PHP_URL_HOST) ?: $entry) : null,
            (array) config('turbo.trusted_redirect_hosts', []),
        );

        return array_values(array_unique(array_filter(array_merge(
            [
                request()->getHost(),
                parse_url((string) config('app.url'), PHP_URL_HOST),
            ],
            $configured,
        ))));
    }
}
