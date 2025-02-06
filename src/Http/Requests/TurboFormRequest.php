<?php

namespace Emaia\LaravelTurbo\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class TurboFormRequest extends FormRequest
{
    protected function failedValidation(Validator $validator): void
    {
        if (request()->wasFromTurboFrame('modal')) {
            $this->redirect = url(session('_previous.url'));
        }

        parent::failedValidation($validator);
    }
}
