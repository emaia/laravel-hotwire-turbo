<?php

use Emaia\LaravelHotwireTurbo\Response;
use Emaia\LaravelHotwireTurbo\StreamInterface;
use Emaia\LaravelHotwireTurbo\TurboStreamBuilder;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

class BuilderTestMessage extends Model
{
    protected $guarded = [];

    protected $table = 'messages';
}

it('chains multiple stream actions', function () {
    $builder = turbo_stream()
        ->append('messages', '<p>Hello</p>')
        ->remove('modal')
        ->update('counter', '<span>5</span>');

    $html = $builder->render();

    expect($html)
        ->toContain('action="append"')
        ->toContain('action="remove"')
        ->toContain('action="update"');
});

it('returns a TurboResponse from withResponse()', function () {
    $response = turbo_stream()
        ->append('messages', '<p>New</p>')
        ->withResponse();

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and($response->headers->get('Content-Type'))->toBe('text/vnd.turbo-stream.html')
        ->and($response->getStatusCode())->toBe(200);
});

it('accepts custom status code in withResponse()', function () {
    $response = turbo_stream()
        ->replace('form', '<form></form>')
        ->withResponse(422);

    expect($response->getStatusCode())->toBe(422);
});

it('supports all stream actions', function () {
    $html = turbo_stream()
        ->append('a', 'c')
        ->prepend('b', 'c')
        ->replace('c', 'c')
        ->update('d', 'c')
        ->remove('e')
        ->after('f', 'c')
        ->before('g', 'c')
        ->refresh()
        ->render();

    expect($html)
        ->toContain('action="append"')
        ->toContain('action="prepend"')
        ->toContain('action="replace"')
        ->toContain('action="update"')
        ->toContain('action="remove"')
        ->toContain('action="after"')
        ->toContain('action="before"')
        ->toContain('action="refresh"');
});

it('supports replace with method morph', function () {
    $html = turbo_stream()
        ->replace('card', '<p>New</p>', method: 'morph')
        ->render();

    expect($html)
        ->toContain('action="replace"')
        ->toContain('method="morph"');
});

it('supports update with method morph', function () {
    $html = turbo_stream()
        ->update('list', '<li>Item</li>', method: 'morph')
        ->render();

    expect($html)
        ->toContain('action="update"')
        ->toContain('method="morph"');
});

it('supports refresh with method, scroll and requestId', function () {
    $html = turbo_stream()
        ->refresh(method: 'morph', scroll: 'preserve', requestId: 'req-1')
        ->render();

    expect($html)
        ->toContain('action="refresh"')
        ->toContain('method="morph"')
        ->toContain('scroll="preserve"')
        ->toContain('request-id="req-1"');
});

it('supports when() conditional chaining', function () {
    $html = turbo_stream()
        ->append('messages', '<p>Hello</p>')
        ->when(true, fn ($b) => $b->update('counter', '<span>5</span>'))
        ->when(false, fn ($b) => $b->remove('should-not-exist'))
        ->render();

    expect($html)
        ->toContain('action="append"')
        ->toContain('action="update"')
        ->not->toContain('should-not-exist');
});

it('supports unless() conditional chaining', function () {
    $html = turbo_stream()
        ->append('messages', '<p>Hello</p>')
        ->unless(false, fn ($b) => $b->update('counter', '<span>5</span>'))
        ->unless(true, fn ($b) => $b->remove('should-not-exist'))
        ->render();

    expect($html)
        ->toContain('action="append"')
        ->toContain('action="update"')
        ->not->toContain('should-not-exist');
});

it('accepts model as target in builder methods', function () {
    $model = new BuilderTestMessage;
    $model->id = 7;

    $html = turbo_stream()
        ->append($model, '<p>Hello</p>')
        ->remove($model)
        ->render();

    expect($html)
        ->toContain('target="builder_test_message_7"');
});

it('accepts model in replace with morph', function () {
    $model = new BuilderTestMessage;
    $model->id = 3;

    $html = turbo_stream()
        ->replace($model, '<div>New</div>', method: 'morph')
        ->render();

    expect($html)
        ->toContain('target="builder_test_message_3"')
        ->toContain('method="morph"');
});

it('supports appendAll with CSS selector', function () {
    $html = turbo_stream()->appendAll('.items', '<li>New</li>')->render();
    expect($html)->toContain('action="append"')->toContain('targets=".items"');
});

it('supports prependAll with CSS selector', function () {
    $html = turbo_stream()->prependAll('.lists', '<li>First</li>')->render();
    expect($html)->toContain('action="prepend"')->toContain('targets=".lists"');
});

it('supports replaceAll with CSS selector', function () {
    $html = turbo_stream()->replaceAll('.card', '<div>New</div>')->render();
    expect($html)->toContain('action="replace"')->toContain('targets=".card"');
});

it('supports updateAll with CSS selector', function () {
    $html = turbo_stream()->updateAll('.badge', '<span>5</span>')->render();
    expect($html)->toContain('action="update"')->toContain('targets=".badge"');
});

it('supports removeAll with CSS selector', function () {
    $html = turbo_stream()->removeAll('.flash')->render();
    expect($html)->toContain('action="remove"')->toContain('targets=".flash"');
});

it('supports afterAll with CSS selector', function () {
    $html = turbo_stream()->afterAll('.item', '<hr>')->render();
    expect($html)->toContain('action="after"')->toContain('targets=".item"');
});

it('supports beforeAll with CSS selector', function () {
    $html = turbo_stream()->beforeAll('.entry', '<hr>')->render();
    expect($html)->toContain('action="before"')->toContain('targets=".entry"');
});

it('supports replaceAll with morph', function () {
    $html = turbo_stream()->replaceAll('.card', '<div>New</div>', method: 'morph')->render();
    expect($html)->toContain('targets=".card"')->toContain('method="morph"');
});

it('supports updateAll with morph', function () {
    $html = turbo_stream()->updateAll('.badge', '<span>5</span>', method: 'morph')->render();
    expect($html)->toContain('targets=".badge"')->toContain('method="morph"');
});

it('chains multiple *All methods', function () {
    $html = turbo_stream()
        ->appendAll('.notifications', '<p>Alert</p>')
        ->updateAll('.badge', '<span>5</span>')
        ->removeAll('.flash')
        ->render();

    expect($html)
        ->toContain('targets=".notifications"')
        ->toContain('targets=".badge"')
        ->toContain('targets=".flash"')
        ->not->toContain('target=');
});

it('implements StreamInterface', function () {
    $builder = turbo_stream()->append('x', 'y');

    expect($builder)->toBeInstanceOf(StreamInterface::class);
});

it('works with response()->turboStream() macro', function () {
    $builder = turbo_stream()->append('x', 'y');
    $response = response()->turboStream($builder); // @phpstan-ignore method.notFound

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and($response->getContent())->toContain('action="append"');
});

it('returns instance from helper function', function () {
    expect(turbo_stream())->toBeInstanceOf(TurboStreamBuilder::class);
});

it('implements Responsable', function () {
    $builder = turbo_stream()->append('x', 'y');

    expect($builder)->toBeInstanceOf(Responsable::class);
});

it('supports custom macros', function () {
    TurboStreamBuilder::macro('closeModal', function () {
        return $this->append('modal', '<span data-controller="dialog--closemodal"></span>');
    });

    $html = turbo_stream()
        ->append('messages', '<p>Hello</p>')
        ->closeModal()
        ->render();

    expect($html)
        ->toContain('action="append"')
        ->toContain('target="messages"')
        ->toContain('target="modal"')
        ->toContain('data-controller="dialog--closemodal"');
});

it('supports macros with parameters', function () {
    TurboStreamBuilder::macro('notification', function (string $type, string $text) {
        return $this->append('notifications', "<div class=\"alert-{$type}\">{$text}</div>");
    });

    $html = turbo_stream()
        ->notification('success', 'Saved!')
        ->render();

    expect($html)
        ->toContain('target="notifications"')
        ->toContain('alert-success')
        ->toContain('Saved!');
});

it('chains macros with built-in methods', function () {
    TurboStreamBuilder::macro('done', function () {
        return $this->remove('spinner');
    });

    $html = turbo_stream()
        ->replace('form', '<form>Updated</form>')
        ->done()
        ->render();

    expect($html)
        ->toContain('action="replace"')
        ->toContain('target="form"')
        ->toContain('action="remove"')
        ->toContain('target="spinner"');
});

it('can be returned directly as a response', function () {
    Route::get('/turbo-stream-direct', function () {
        return turbo_stream()->append('messages', '<p>Hello</p>');
    });

    $response = $this->get('/turbo-stream-direct');

    expect($response->headers->get('Content-Type'))->toContain('text/vnd.turbo-stream.html')
        ->and($response->getContent())->toContain('action="append"');
});

describe('view() and partial() builder helpers', function () {
    it('attaches a view to the last stream', function () {
        $view = makeTempBladeView('<li>{{ $name }}</li>');

        $html = turbo_stream()
            ->append('messages')
            ->view($view, ['name' => 'Alice'])
            ->render();

        expect($html)
            ->toContain('action="append"')
            ->toContain('target="messages"')
            ->toContain('<li>Alice</li>');
    });

    it('partial() is an alias of view() on the builder', function () {
        $view = makeTempBladeView('<span>{{ $count }}</span>');

        $html = turbo_stream()
            ->update('counter')
            ->partial($view, ['count' => 3])
            ->render();

        expect($html)->toContain('<span>3</span>');
    });

    it('chains view() between multiple streams', function () {
        $view = makeTempBladeView('<p>{{ $body }}</p>');

        $html = turbo_stream()
            ->append('messages')->view($view, ['body' => 'first'])
            ->update('counter')->view($view, ['body' => 'second'])
            ->render();

        expect($html)
            ->toContain('target="messages"')
            ->toContain('<p>first</p>')
            ->toContain('target="counter"')
            ->toContain('<p>second</p>');
    });

    it('throws when view() is called before any stream is added', function () {
        turbo_stream()->view('whatever');
    })->throws(LogicException::class, 'Cannot call view() on TurboStreamBuilder before adding a stream.');

    it('throws when partial() is called before any stream is added', function () {
        turbo_stream()->partial('whatever');
    })->throws(LogicException::class, 'Cannot call view() on TurboStreamBuilder before adding a stream.');
});

describe('escape() builder helper', function () {
    it('escapes the last stream content', function () {
        $html = turbo_stream()
            ->update('greeting', '<script>x</script>')
            ->escape()
            ->render();

        expect($html)
            ->toContain('&lt;script&gt;')
            ->not->toContain('<script>x');
    });

    it('only applies to the most recently added stream', function () {
        $html = turbo_stream()
            ->update('a', '<b>raw</b>')
            ->update('b', '<b>escaped</b>')
            ->escape()
            ->render();

        expect($html)
            ->toContain('<b>raw</b>')
            ->toContain('&lt;b&gt;escaped&lt;/b&gt;');
    });

    it('throws when escape() is called before any stream is added', function () {
        turbo_stream()->escape();
    })->throws(LogicException::class, 'Cannot call escape() on TurboStreamBuilder before adding a stream.');
});

describe('renderable contracts', function () {
    it('implements Htmlable, Responsable, StreamInterface and Stringable', function () {
        $builder = turbo_stream()->append('messages', '<p>Hi</p>');

        expect($builder)
            ->toBeInstanceOf(Htmlable::class)
            ->toBeInstanceOf(Responsable::class)
            ->toBeInstanceOf(StreamInterface::class)
            ->toBeInstanceOf(Stringable::class);
    });

    it('returns the rendered html string from toHtml()', function () {
        $builder = turbo_stream()->append('messages', '<p>Hi</p>');

        expect($builder->toHtml())
            ->toBeString()
            ->toContain('action="append"');
    });

    it('renders to string when echoed', function () {
        $builder = turbo_stream()->append('messages', '<p>Hi</p>')->remove('modal');

        expect((string) $builder)
            ->toContain('action="append"')
            ->toContain('action="remove"');
    });

    it('can be echoed in Blade without escaping the turbo-stream tags', function () {
        $builder = turbo_stream()->append('messages', '<p>Hi</p>');

        $html = Blade::render('{{ $stream }}', ['stream' => $builder]);

        expect($html)
            ->toContain('<turbo-stream')
            ->toContain('action="append"')
            ->not->toContain('&lt;turbo-stream');
    });
});
