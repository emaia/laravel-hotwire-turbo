<?php

use Emaia\LaravelHotwireTurbo\Http\Requests\TurboFormRequest;
use Emaia\LaravelHotwireTurbo\Testing\InteractsWithTurbo;
use Illuminate\Support\Facades\Route;

uses(InteractsWithTurbo::class);

class ProfileUpdateRequest extends TurboFormRequest
{
    public function rules(): array
    {
        return ['name' => 'required'];
    }
}

beforeEach(function () {
    Route::post('/profile', function (ProfileUpdateRequest $request) {
        // Validation failed, never reached
    });
});

it('redirects to _turbo_frame_src input when present in turbo frame request', function () {
    $response = $this->fromTurboFrame('profile-form')
        ->post('/profile', [
            'name' => '',
            '_turbo_frame_src' => url('/profile'),
        ]);

    $response->assertRedirect('/profile');
});

it('falls back to Referer header when _turbo_frame_src is absent', function () {
    $response = $this->fromTurboFrame('profile-form')
        ->post('/profile', ['name' => ''], ['Referer' => url('/dashboard')]);

    $response->assertRedirect('/dashboard');
});

it('falls back to url()->previous() when no input or referer', function () {
    session()->setPreviousUrl(url('/fallback'));

    $response = $this->fromTurboFrame('profile-form')
        ->post('/profile', ['name' => '']);

    $response->assertRedirect('/fallback');
});

it('accepts relative URL in _turbo_frame_src', function () {
    $response = $this->fromTurboFrame('profile-form')
        ->post('/profile', [
            'name' => '',
            '_turbo_frame_src' => '/profile?tab=settings',
        ]);

    $response->assertRedirect('/profile?tab=settings');
});

it('rejects external URL in _turbo_frame_src to prevent open redirect', function () {
    $response = $this->fromTurboFrame('profile-form')
        ->post('/profile', [
            'name' => '',
            '_turbo_frame_src' => 'https://evil.com/phishing',
        ]);

    $response->assertRedirect('/');
});

it('does not alter redirect for non-turbo-frame requests', function () {
    $response = $this->post('/profile', ['name' => '']);

    $response->assertRedirect('/');
});
