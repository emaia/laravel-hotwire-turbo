<?php

namespace Emaia\LaravelHotwireTurbo\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class TurboFormRequest extends FormRequest
{
    protected function failedValidation(Validator $validator): void
    {
        if (request()->wasFromTurboFrame()) {
            $this->redirect = url(session('_previous.url'));
        }

        parent::failedValidation($validator);
    }
}
