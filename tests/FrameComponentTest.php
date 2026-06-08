<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;

class FrameTestMessage extends Model
{
    protected $guarded = [];

    protected $table = 'messages';
}

it('renders a basic turbo frame', function () {
    $html = Blade::render('<x-turbo::frame id="my-frame">Content</x-turbo::frame>');

    expect($html)
        ->toContain('<turbo-frame id="my-frame"')
        ->toContain('Content')
        ->toContain('</turbo-frame>');
});

it('renders with src attribute', function () {
    $html = Blade::render('<x-turbo::frame id="lazy" src="/load">Loading...</x-turbo::frame>');

    expect($html)
        ->toContain('id="lazy"')
        ->toContain('src="/load"');
});

it('renders with loading="lazy"', function () {
    $html = Blade::render('<x-turbo::frame id="lazy" src="/load" loading="lazy">Loading...</x-turbo::frame>');

    expect($html)->toContain('loading="lazy"');
});

it('renders with target attribute', function () {
    $html = Blade::render('<x-turbo::frame id="nav" target="_top">Link</x-turbo::frame>');

    expect($html)->toContain('target="_top"');
});

it('renders with disabled attribute', function () {
    $html = Blade::render('<x-turbo::frame id="locked" :disabled="true">Locked</x-turbo::frame>');

    expect($html)->toContain('disabled');
});

it('renders slot content', function () {
    $html = Blade::render('<x-turbo::frame id="test"><p>Inner HTML</p></x-turbo::frame>');

    expect($html)->toContain('<p>Inner HTML</p>');
});

it('renders with refresh="morph" for morphing page refreshes', function () {
    $html = Blade::render('<x-turbo::frame id="my-frame" src="/data" refresh="morph" />');

    expect($html)->toContain('refresh="morph"');
});

it('renders with autoscroll attribute', function () {
    $html = Blade::render('<x-turbo::frame id="feed" src="/feed" :autoscroll="true" />');

    expect($html)->toContain('autoscroll');
});

it('renders with data-autoscroll-block', function () {
    $html = Blade::render('<x-turbo::frame id="feed" src="/feed" autoscroll-block="start" />');

    expect($html)->toContain('data-autoscroll-block="start"');
});

it('renders with data-autoscroll-behavior', function () {
    $html = Blade::render('<x-turbo::frame id="feed" src="/feed" autoscroll-behavior="smooth" />');

    expect($html)->toContain('data-autoscroll-behavior="smooth"');
});

it('renders with data-turbo-action for history promotion', function () {
    $html = Blade::render('<x-turbo::frame id="pager" advance="advance">Next</x-turbo::frame>');

    expect($html)->toContain('data-turbo-action="advance"');
});

it('renders with recurse attribute', function () {
    $html = Blade::render('<x-turbo::frame id="recursive" src="/frame" recurse="composer" />');

    expect($html)->toContain('recurse="composer"');
});

it('passes through arbitrary extra attributes', function () {
    $html = Blade::render('<x-turbo::frame id="test" class="mt-4" data-controller="foo">Hi</x-turbo::frame>');

    expect($html)
        ->toContain('class="mt-4"')
        ->toContain('data-controller="foo"');
});

it('does not emit omitted optional attributes', function () {
    $html = Blade::render('<x-turbo::frame id="clean">Content</x-turbo::frame>');

    expect($html)
        ->not->toContain('src=')
        ->not->toContain('loading=')
        ->not->toContain('target=')
        ->not->toContain('refresh=')
        ->not->toContain('recurse=')
        ->not->toContain('autoscroll')
        ->not->toContain('disabled');
});

describe('model-aware id', function () {
    it('resolves an Eloquent model to its dom_id', function () {
        $model = new FrameTestMessage;
        $model->id = 7;

        $html = Blade::render(
            '<x-turbo::frame :id="$model">Hello</x-turbo::frame>',
            ['model' => $model],
        );

        expect($html)->toContain('id="frame_test_message_7"');
    });

    it('resolves a new model with no key to its create prefix', function () {
        $html = Blade::render(
            '<x-turbo::frame :id="$model">Form</x-turbo::frame>',
            ['model' => new FrameTestMessage],
        );

        expect($html)->toContain('id="create_frame_test_message"');
    });

    it('still accepts string ids', function () {
        $html = Blade::render('<x-turbo::frame id="literal">Hi</x-turbo::frame>');

        expect($html)->toContain('id="literal"');
    });
});
