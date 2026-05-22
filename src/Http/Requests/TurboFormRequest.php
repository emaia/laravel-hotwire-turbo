<?php

namespace Emaia\LaravelHotwireTurbo\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

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
                ?: $this->header('Referer')
                ?: url()->previous()
        );
    }

    private function sanitizeRedirectUrl(string $url): string
    {
        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        $host = parse_url($url, PHP_URL_HOST);

        if ($host === null || $host === $appHost) {
            return $url;
        }

        return url('/');
    }
}
