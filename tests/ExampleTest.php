<?php

use Emaia\LaravelHotwireTurbo\Enums\Action;
use Emaia\LaravelHotwireTurbo\Stream;
use Illuminate\Support\Facades\Blade;

it('can render a Turbo Stream', function () {

    $view = Blade::render('<div id="element-id">Is featured</div>', ['is_featured' => true]);

    $stream = new Stream(
        Action::UPDATE,
        'target-element-id',
        $view
    );

    $expected = <<<'HTML'
                <turbo-stream action="update" target="target-element-id">
                    <template>
                        <div id="element-id">Is featured</div>
                    </template>
                </turbo-stream>

                HTML;

    expect($stream->render())->toBe($expected);
});
