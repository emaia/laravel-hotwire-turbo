<?php

use Emaia\LaravelHotwireTurbo\Enums\Action;
use Emaia\LaravelHotwireTurbo\Stream;
use Emaia\LaravelHotwireTurbo\StreamInterface;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;

class StreamTestMessage extends Model
{
    protected $guarded = [];

    protected $table = 'messages';
}

it('renders a turbo stream with target', function () {
    $stream = new Stream(Action::UPDATE, 'my-target', '<p>Hello</p>');
    $html = $stream->render();

    expect($html)
        ->toContain('action="update"')
        ->toContain('target="my-target"')
        ->toContain('<p>Hello</p>');
});

it('renders a turbo stream with targets css selector', function () {
    $stream = new Stream(Action::UPDATE, targets: '.my-class', content: '<p>Hello</p>');
    $html = $stream->render();

    expect($html)
        ->toContain('action="update"')
        ->toContain('targets=".my-class"')
        ->toContain('<p>Hello</p>');
});

it('throws exception when neither target nor targets provided', function () {
    new Stream(Action::UPDATE, '', '');
})->throws(InvalidArgumentException::class, 'Either target or targets must be provided');

it('renders a view as content', function () {
    $view = Blade::render('<div>{{ $name }}</div>', ['name' => 'Test']);
    $stream = new Stream(Action::REPLACE, 'box', $view);

    expect($stream->render())->toContain('<div>Test</div>');
});

it('renders remove action without template', function () {
    $stream = Stream::remove('my-target');
    $html = $stream->render();

    expect($html)
        ->toContain('action="remove"')
        ->toContain('target="my-target"')
        ->not->toContain('<template>');
});

it('renders refresh action without template or target', function () {
    $html = Stream::refresh()->render();

    expect($html)
        ->toContain('action="refresh"')
        ->not->toContain('target=')
        ->not->toContain('<template>');
});

it('allows refresh action with no target without throwing', function () {
    $stream = new Stream(Action::REFRESH);

    expect($stream->render())->toContain('action="refresh"');
});

it('normalizes case-insensitive standard action strings to lowercase', function () {
    $stream = new Stream('APPEND', 'target', 'content');

    expect($stream->render())->toContain('action="append"');
});

it('allows refresh via case-insensitive string without throwing', function () {
    $stream = new Stream('Refresh');

    expect($stream->render())->toContain('action="refresh"');
});

it('preserves custom action strings as-is', function () {
    $stream = new Stream('console-log', 'target', 'content');

    expect($stream->render())->toContain('action="console-log"');
});

it('supports all turbo 8 actions', function (Action $action) {
    $stream = new Stream($action, 'target', 'content');

    expect($stream->render())->toContain("action=\"{$action->value}\"");
})->with([
    Action::APPEND,
    Action::PREPEND,
    Action::REPLACE,
    Action::UPDATE,
    Action::REMOVE,
    Action::AFTER,
    Action::BEFORE,
    Action::REFRESH,
]);

describe('fluent factory methods', function () {
    it('creates append stream', function () {
        $html = Stream::append('target', 'content')->render();
        expect($html)->toContain('action="append"')->toContain('target="target"');
    });

    it('creates prepend stream', function () {
        $html = Stream::prepend('target', 'content')->render();
        expect($html)->toContain('action="prepend"');
    });

    it('creates replace stream', function () {
        $html = Stream::replace('target', 'content')->render();
        expect($html)->toContain('action="replace"');
    });

    it('creates update stream', function () {
        $html = Stream::update('target', 'content')->render();
        expect($html)->toContain('action="update"');
    });

    it('creates remove stream', function () {
        $html = Stream::remove('target')->render();
        expect($html)->toContain('action="remove"');
    });

    it('creates after stream', function () {
        $html = Stream::after('target', 'content')->render();
        expect($html)->toContain('action="after"');
    });

    it('creates before stream', function () {
        $html = Stream::before('target', 'content')->render();
        expect($html)->toContain('action="before"');
    });

    it('creates refresh stream', function () {
        $html = Stream::refresh()->render();
        expect($html)->toContain('action="refresh"');
    });

    it('creates replace stream with method morph', function () {
        $html = Stream::replace('target', 'content', method: 'morph')->render();
        expect($html)
            ->toContain('action="replace"')
            ->toContain('method="morph"');
    });

    it('creates update stream with method morph', function () {
        $html = Stream::update('target', 'content', method: 'morph')->render();
        expect($html)
            ->toContain('action="update"')
            ->toContain('method="morph"');
    });

    it('creates refresh stream with method and scroll', function () {
        $html = Stream::refresh(method: 'morph', scroll: 'preserve')->render();
        expect($html)
            ->toContain('action="refresh"')
            ->toContain('method="morph"')
            ->toContain('scroll="preserve"');
    });

    it('creates refresh stream with request-id', function () {
        $html = Stream::refresh(requestId: 'abc-123')->render();
        expect($html)
            ->toContain('action="refresh"')
            ->toContain('request-id="abc-123"');
    });
});

describe('*All factory methods with CSS selectors', function () {
    it('creates appendAll stream', function () {
        $html = Stream::appendAll('.items', '<li>New</li>')->render();
        expect($html)
            ->toContain('action="append"')
            ->toContain('targets=".items"')
            ->not->toContain('target=');
    });

    it('creates prependAll stream', function () {
        $html = Stream::prependAll('.lists', '<li>First</li>')->render();
        expect($html)->toContain('action="prepend"')->toContain('targets=".lists"');
    });

    it('creates replaceAll stream with morph', function () {
        $html = Stream::replaceAll('.card', '<div>New</div>', method: 'morph')->render();
        expect($html)
            ->toContain('action="replace"')
            ->toContain('targets=".card"')
            ->toContain('method="morph"');
    });

    it('creates updateAll stream', function () {
        $html = Stream::updateAll('.badge', '<span>5</span>')->render();
        expect($html)->toContain('action="update"')->toContain('targets=".badge"');
    });

    it('creates removeAll stream', function () {
        $html = Stream::removeAll('.flash')->render();
        expect($html)
            ->toContain('action="remove"')
            ->toContain('targets=".flash"');
    });

    it('creates afterAll stream', function () {
        $html = Stream::afterAll('.item', '<hr>')->render();
        expect($html)->toContain('action="after"')->toContain('targets=".item"');
    });

    it('creates beforeAll stream', function () {
        $html = Stream::beforeAll('.item', '<hr>')->render();
        expect($html)->toContain('action="before"')->toContain('targets=".item"');
    });
});

describe('model-aware targets', function () {
    it('resolves model to dom_id in append', function () {
        $model = new StreamTestMessage;
        $model->id = 42;

        $html = Stream::append($model, '<p>Hi</p>')->render();

        expect($html)
            ->toContain('target="stream_test_message_42"')
            ->toContain('<p>Hi</p>');
    });

    it('resolves new model to create prefix', function () {
        $model = new StreamTestMessage;

        $html = Stream::replace($model, '<form></form>')->render();

        expect($html)->toContain('target="create_stream_test_message"');
    });

    it('throws for objects without getKey', function () {
        Stream::append(new stdClass, 'content');
    })->throws(InvalidArgumentException::class);

    it('still accepts string targets', function () {
        $html = Stream::append('my-target', 'content')->render();

        expect($html)->toContain('target="my-target"');
    });
});

describe('renderable contracts', function () {
    it('implements Htmlable, StreamInterface and Stringable', function () {
        $stream = Stream::append('messages', '<p>Hi</p>');

        expect($stream)
            ->toBeInstanceOf(Htmlable::class)
            ->toBeInstanceOf(StreamInterface::class)
            ->toBeInstanceOf(Stringable::class);
    });

    it('returns the rendered html string from toHtml()', function () {
        $stream = Stream::append('messages', '<p>Hi</p>');

        expect($stream->toHtml())
            ->toBeString()
            ->toContain('action="append"')
            ->toContain('target="messages"');
    });

    it('renders to string when echoed', function () {
        $stream = Stream::append('messages', '<p>Hi</p>');

        expect((string) $stream)->toContain('action="append"');
    });

    it('can be echoed in Blade without escaping the turbo-stream tag', function () {
        $stream = Stream::append('messages', '<p>Hi</p>');

        $html = Blade::render('{{ $stream }}', ['stream' => $stream]);

        expect($html)
            ->toContain('<turbo-stream')
            ->toContain('action="append"')
            ->not->toContain('&lt;turbo-stream');
    });
});

describe('view() and partial() content helpers', function () {
    it('sets the content from a Blade view', function () {
        $view = makeTempBladeView('<li>{{ $name }}</li>');

        $html = Stream::append('messages')
            ->view($view, ['name' => 'Bob'])
            ->render();

        expect($html)
            ->toContain('action="append"')
            ->toContain('target="messages"')
            ->toContain('<li>Bob</li>');
    });

    it('partial() is an alias of view()', function () {
        $view = makeTempBladeView('<span>{{ $count }}</span>');

        $html = Stream::update('counter')
            ->partial($view, ['count' => 7])
            ->render();

        expect($html)->toContain('<span>7</span>');
    });

    it('overwrites previous content when called', function () {
        $view = makeTempBladeView('<p>fresh</p>');

        $html = Stream::append('messages', '<p>stale</p>')
            ->view($view, [])
            ->render();

        expect($html)
            ->toContain('<p>fresh</p>')
            ->not->toContain('<p>stale</p>');
    });
});

describe('escape() content flag', function () {
    it('escapes string content when enabled', function () {
        $html = Stream::update('greeting', '<script>alert(1)</script>')
            ->escape()
            ->render();

        expect($html)
            ->toContain('&lt;script&gt;')
            ->not->toContain('<script>alert');
    });

    it('does not escape by default', function () {
        $html = Stream::update('greeting', '<strong>hi</strong>')->render();

        expect($html)
            ->toContain('<strong>hi</strong>')
            ->not->toContain('&lt;strong&gt;');
    });

    it('escape(false) keeps content raw', function () {
        $html = Stream::update('greeting', '<strong>hi</strong>')
            ->escape(false)
            ->render();

        expect($html)->toContain('<strong>hi</strong>');
    });

    it('does not double-escape view-rendered content', function () {
        // view() output is already HTML; escape() would double-escape it.
        // This documents that escape() is intended for user-supplied strings.
        $view = makeTempBladeView('<p>safe</p>');

        $html = Stream::update('greeting')->view($view, [])->render();

        expect($html)->toContain('<p>safe</p>');
    });
});

describe('Stream::macro', function () {
    it('registers and invokes a custom factory macro', function () {
        Stream::macro('confetti', function (string $target) {
            return Stream::action('confetti', $target, '', ['data-duration' => '2000']);
        });

        $stream = Stream::confetti('party');

        expect($stream)->toBeInstanceOf(Stream::class);
        expect($stream->render())
            ->toContain('action="confetti"')
            ->toContain('target="party"')
            ->toContain('data-duration="2000"');
    });

    it('supports macros invoked on instances', function () {
        Stream::macro('withDebugAttribute', function () {
            /** @var Stream $this */
            return $this;
        });

        $stream = Stream::append('messages', '<p>Hi</p>')->withDebugAttribute();

        expect($stream)->toBeInstanceOf(Stream::class);
    });
});

describe('stream component blade usage', function () {
    it('renders with method="morph" for morphing replace', function () {
        $html = Blade::render('<x-turbo::stream action="replace" target="card" method="morph"><p>New</p></x-turbo::stream>');

        expect($html)
            ->toContain('action="replace"')
            ->toContain('method="morph"')
            ->toContain('<p>New</p>');
    });

    it('renders with method="morph" for morphing update', function () {
        $html = Blade::render('<x-turbo::stream action="update" target="list" method="morph"><li>X</li></x-turbo::stream>');

        expect($html)
            ->toContain('action="update"')
            ->toContain('method="morph"');
    });

    it('renders refresh with request-id for debouncing', function () {
        $html = Blade::render('<x-turbo::stream action="refresh" request-id="abcd-1234" />');

        expect($html)
            ->toContain('action="refresh"')
            ->toContain('request-id="abcd-1234"')
            ->not->toContain('<template>');
    });

    it('renders refresh with method and scroll', function () {
        $html = Blade::render('<x-turbo::stream action="refresh" method="morph" scroll="preserve" />');

        expect($html)
            ->toContain('method="morph"')
            ->toContain('scroll="preserve"')
            ->not->toContain('<template>');
    });

    it('accepts Action enum for action prop', function () {
        $html = Blade::render(
            '<x-turbo::stream :action="$action" target="box">Hi</x-turbo::stream>',
            ['action' => Action::APPEND],
        );

        expect($html)->toContain('action="append"');
    });

    it('passes through arbitrary extra attributes', function () {
        $html = Blade::render('<x-turbo::stream action="append" target="list" data-controller="logger">Hi</x-turbo::stream>');

        expect($html)->toContain('data-controller="logger"');
    });

    it('does not emit omitted optional attributes', function () {
        $html = Blade::render('<x-turbo::stream action="remove" target="item" />');

        expect($html)
            ->not->toContain('method=')
            ->not->toContain('scroll=')
            ->not->toContain('request-id=');
    });
});
