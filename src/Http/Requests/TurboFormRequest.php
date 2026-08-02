<?php

namespace Emaia\LaravelHotwireTurbo\Http\Requests;

use Emaia\LaravelHotwireTurbo\Http\FrameSourceResolver;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class TurboFormRequest extends FormRequest
{
    protected function failedValidation(Validator $validator): void
    {
        if ($this->wasFromTurboFrame()) {
            $this->redirect = app(FrameSourceResolver::class)->resolveValidationRedirect($this);
        }

        parent::failedValidation($validator);
    }
}
