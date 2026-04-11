# Hotwire Turbo with Laravel!

[![Latest Version on Packagist](https://img.shields.io/packagist/v/emaia/laravel-hotwire-turbo.svg?style=flat-square)](https://packagist.org/packages/emaia/laravel-hotwire-turbo)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/emaia/laravel-hotwire-turbo/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/emaia/laravel-hotwire-turbo/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/emaia/laravel-hotwire-turbo/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/emaia/laravel-hotwire-turbo/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/emaia/laravel-hotwire-turbo.svg?style=flat-square)](https://packagist.org/packages/emaia/laravel-hotwire-turbo)

The purpose of this package is to facilitate the use of [Turbo](https://turbo.hotwired.dev/) ([Hotwire](https://hotwired.dev/)) in a Laravel app.

## Table of Contents

- [Installation](#installation)
- [Usage](#usage)
  - [Turbo Stream Actions](#turbo-stream-actions)
  - [Fluent Builder](#fluent-builder-recommended)
  - [DOM Identification](#dom-identification)
  - [Creating Individual Streams](#creating-individual-streams)
  - [Targeting Multiple Elements](#targeting-multiple-elements-css-selector)
  - [Stream Collections](#stream-collections)
  - [Turbo Stream Responses](#turbo-stream-responses)
  - [Turbo Stream Views](#turbo-stream-views)
  - [Conditional Turbo Responses](#conditional-turbo-responses)
  - [Custom Stream Actions](#custom-stream-actions)
  - [Detecting Turbo Requests](#detecting-turbo-requests)
  - [Form Validation with Turbo Frames](#form-validation-with-turbo-frames)
  - [Blade Components](#blade-components)
    - [Turbo Stream](#turbo-stream)
    - [Turbo Frame](#turbo-frame)
  - [Turbo Drive Blade Directives](#turbo-drive-blade-directives)
  - [Full Controller Example](#full-controller-example)
- [Testing](#testing)
- [Running Tests](#running-tests)

## Installation

```bash
composer require emaia/laravel-hotwire-turbo
```

## Usage

### Turbo Stream Actions

All Turbo 8 stream actions are supported:

| Action | Description |
|--------|-------------|
| `append` | Add content after the target's existing content |
| `prepend` | Add content before the target's existing content |
| `replace` | Replace the entire target element |
| `update` | Update the target element's content |
| `remove` | Remove the target element |
| `after` | Insert content after the target element |
| `before` | Insert content before the target element |
| `morph` | Morph the target element to the new content |
| `refresh` | Trigger a page refresh |

### Fluent Builder (Recommended)

The `turbo_stream()` helper provides a chainable API with zero imports:

```php
return turbo_stream()
    ->append('messages', view('messages.item', compact('message')))
    ->remove('modal')
    ->update('counter', '<span>42</span>');
```

Use `withResponse()` when you need custom status code or headers:

```php
return turbo_stream()
    ->replace('form', view('form', ['errors' => $errors]))
    ->withResponse(422);
```

### DOM Identification

Generate consistent DOM IDs and CSS classes from your Eloquent models:

```php
$message = Message::find(15);

dom_id($message)            // "message_15"
dom_id($message, 'edit')    // "edit_message_15"
dom_class($message)         // "message"
dom_class($message, 'edit') // "edit_message"

// New records (no key yet)
dom_id(new Message)          // "create_message"
dom_id(new Message, 'new')   // "new_message"
```

Use in Blade templates with the `@domid` and `@domclass` directives:

```blade
<div id="@domid($message)">
    {{ $message->body }}
</div>

<div id="@domid($message, 'edit')" class="@domclass($message)">
    {{-- edit form --}}
</div>
```

Combine with streams for consistent targeting:

```php
return turbo_stream()
    ->append('messages', view('messages.item', compact('message')))
    ->remove(dom_id($message, 'form'));
```

### Creating Individual Streams

Use the fluent static methods on `Stream`:

```php
use Emaia\LaravelHotwireTurbo\Stream;

Stream::append('messages', view('chat.message', ['message' => $message]))
Stream::prepend('notifications', '<div class="alert">New!</div>')
Stream::replace('user-card', view('users.card', ['user' => $user]))
Stream::update('counter', '<span>42</span>')
Stream::remove('modal')
Stream::after('item-3', view('items.row', ['item' => $item]))
Stream::before('item-3', view('items.row', ['item' => $item]))
Stream::morph('profile', view('users.profile', ['user' => $user]))
Stream::refresh()
```

Or use the constructor with the `Action` enum:

```php
use Emaia\LaravelHotwireTurbo\Enums\Action;
use Emaia\LaravelHotwireTurbo\Stream;

$stream = new Stream(
    action: Action::APPEND,
    target: 'messages',
    content: view('chat.message', ['message' => $message]),
);
```

### Targeting Multiple Elements (CSS Selector)

Use `targets` to target multiple DOM elements via CSS selector:

```php
$stream = new Stream(
    action: Action::UPDATE,
    targets: '.notification-badge',
    content: '<span>5</span>',
);
```

### Stream Collections

Compose multiple streams manually when you need more control:

```php
use Emaia\LaravelHotwireTurbo\StreamCollection;
use Emaia\LaravelHotwireTurbo\Stream;

$streams = new StreamCollection([
    Stream::prepend('flash-container', view('components.flash', ['message' => 'Saved!'])),
    Stream::update('modal', ''),
    Stream::remove('loading-spinner'),
]);

// Or build fluently
$streams = StreamCollection::make()
    ->add(Stream::append('messages', view('chat.message', $message)))
    ->add(Stream::update('unread-count', '<span>0</span>'))
    ->add(Stream::remove('typing-indicator'));

return response()->turboStream($streams);
```

### Turbo Stream Responses

The package adds macros to Laravel's response factory. The `Content-Type: text/vnd.turbo-stream.html` header is set automatically:

```php
// Single stream
return response()->turboStream(
    Stream::replace('todo-item-1', view('todos.item', ['todo' => $todo]))
);

// With custom status code
return response()->turboStream($stream, 422);
```

### Turbo Stream Views

For complex responses with multiple streams, write them in a Blade template and return with `turbo_stream_view()`:

```php
// Controller
return turbo_stream_view('messages.streams.created', compact('message', 'count'));

// Or via macro
return response()->turboStreamView('messages.streams.created', compact('message', 'count'));
```

```blade
{{-- resources/views/messages/streams/created.blade.php --}}
<x-turbo::stream action="append" target="messages">
    @include('messages._message', ['message' => $message])
</x-turbo::stream>

<x-turbo::stream action="update" target="message-count">
    <span>{{ $count }}</span>
</x-turbo::stream>

<x-turbo::stream action="remove" target="new-message-form" />
```

### Conditional Turbo Responses

Use explicit request checks in your controllers to return Turbo Streams only when appropriate:

```php
if (request()->wantsTurboStream()) {
    return turbo_stream()->remove(dom_id($message));
}

return redirect()->route('messages.index');
```

To scope behavior to a specific Turbo Frame:

```php
if (request()->wantsTurboStream() && request()->wasFromTurboFrame('modal')) {
    return turbo_stream()->update('modal-content', view('messages.edit', compact('message')));
}

return view('messages.edit', compact('message'));
```

### Custom Stream Actions

Use `Stream::action()` for custom Turbo Stream actions with arbitrary HTML attributes:

```php
use Emaia\LaravelHotwireTurbo\Stream;

Stream::action('console-log', 'debug', '<p>Debug info</p>', [
    'data-level' => 'info',
]);
// <turbo-stream action="console-log" target="debug" data-level="info">...

// Via the fluent builder
return turbo_stream()
    ->action('notification', 'alerts', '<p>Saved!</p>', ['data-timeout' => '3000'])
    ->remove('modal');
```

### Detecting Turbo Requests

```php
if (request()->wantsTurboStream()) {
    return turbo_stream()
        ->replace('todo-1', view('todos.item', ['todo' => $todo]));
}

return redirect()->back();
```

```php
// Check if the request came from any Turbo Frame
if (request()->wasFromTurboFrame()) {
    // ...
}

// Check if it came from a specific Turbo Frame
if (request()->wasFromTurboFrame('modal')) {
    // ...
}
```

### Form Validation with Turbo Frames

Extend `TurboFormRequest` to handle validation errors correctly within Turbo Frames. When validation fails, it redirects to the previous URL so the frame re-renders with errors:

```php
use Emaia\LaravelHotwireTurbo\Http\Requests\TurboFormRequest;

class UpdateProfileRequest extends TurboFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
        ];
    }
}
```

### Blade Components

#### Turbo Stream

```blade
<x-turbo::stream action="append" target="messages">
    <div class="message">{{ $message->body }}</div>
</x-turbo::stream>

<x-turbo::stream action="remove" target="notification-{{ $id }}" />

{{-- Target multiple elements with CSS selector --}}
<x-turbo::stream action="update" targets=".unread-badge">
    <span>0</span>
</x-turbo::stream>
```

##### Morphing

Use `method="morph"` on `replace` or `update` to apply Turbo's [morphing](https://turbo.hotwired.dev/handbook/page_refreshes) instead of a full DOM replacement:

```blade
{{-- Morph the entire element --}}
<x-turbo::stream action="replace" method="morph" target="user-card">
    @include('users.card', ['user' => $user])
</x-turbo::stream>

{{-- Morph only the children --}}
<x-turbo::stream action="update" method="morph" target="message-list">
    @each('messages.item', $messages, 'message')
</x-turbo::stream>
```

##### Page Refresh

```blade
{{-- Basic refresh --}}
<x-turbo::stream action="refresh" />

{{-- Debounced refresh (multiple identical request-ids are coalesced) --}}
<x-turbo::stream action="refresh" request-id="{{ $requestId }}" />

{{-- Refresh with morphing and scroll preservation --}}
<x-turbo::stream action="refresh" method="morph" scroll="preserve" />
```

##### Props reference

| Prop | Description |
|------|-------------|
| `action` | Stream action — accepts string or `Action` enum |
| `target` | Target DOM id |
| `targets` | CSS selector to target multiple elements |
| `method` | `morph` — use morphing instead of full replacement (replace/update) |
| `scroll` | `preserve` or `reset` — scroll behavior for refresh |
| `request-id` | Debounce key for refresh actions |

Extra attributes are forwarded to the `<turbo-stream>` element (e.g. `data-controller`).

#### Turbo Frame

```blade
{{-- Basic frame --}}
<x-turbo::frame id="user-profile">
    @include('users.profile', ['user' => $user])
</x-turbo::frame>

{{-- Eager-loaded frame --}}
<x-turbo::frame id="inbox" src="/messages">
    <p>Loading...</p>
</x-turbo::frame>

{{-- Lazy-loaded frame (loads when visible in viewport) --}}
<x-turbo::frame id="comments" src="/posts/{{ $post->id }}/comments" loading="lazy">
    <p>Loading comments...</p>
</x-turbo::frame>

{{-- Frame that navigates the whole page by default --}}
<x-turbo::frame id="navigation" target="_top">
    <a href="/dashboard">Dashboard</a>
</x-turbo::frame>

{{-- Disabled frame --}}
<x-turbo::frame id="preview" :disabled="true">
    <p>This frame won't navigate.</p>
</x-turbo::frame>

{{-- Morphed on page refresh (instead of a full replacement) --}}
<x-turbo::frame id="feed" src="/feed" refresh="morph" />

{{-- Scroll into view after load --}}
<x-turbo::frame id="results" src="/search" :autoscroll="true" autoscroll-block="start" autoscroll-behavior="smooth" />

{{-- Promote navigations to browser history --}}
<x-turbo::frame id="pager" advance="advance">
    <a href="?page=2">Next page</a>
</x-turbo::frame>

{{-- Recursive frame --}}
<x-turbo::frame id="recursive" src="/frame" recurse="composer" />
```

##### Props reference

| Prop | Description |
|------|-------------|
| `id` | Frame identifier (required) |
| `src` | URL to load content from (eager by default) |
| `loading` | `eager` (default) or `lazy` |
| `target` | Default navigation target — use `_top` to navigate the whole page |
| `disabled` | Prevents all navigation |
| `refresh` | `morph` — use morphing when the frame reloads on page refresh |
| `autoscroll` | Scroll the frame into view after loading |
| `autoscroll-block` | Vertical alignment: `end` (default), `start`, `center`, `nearest` |
| `autoscroll-behavior` | Scroll animation: `auto` (default) or `smooth` |
| `advance` | `advance` or `replace` — promote navigations to browser history |
| `recurse` | Frame id to recurse into when extracting content |

Extra attributes are forwarded to the `<turbo-frame>` element (e.g. `class`, `data-controller`).

### Turbo Drive Blade Directives

Control Turbo Drive behavior in your layout's `<head>`:

```blade
<head>
    @turboNocache
    @turboNoPreview
    @turboRefreshMethod('morph')
    @turboRefreshScroll('preserve')
</head>
```

| Directive | Output |
|-----------|--------|
| `@turboNocache` | `<meta name="turbo-cache-control" content="no-cache">` |
| `@turboNoPreview` | `<meta name="turbo-cache-control" content="no-preview">` |
| `@turboRefreshMethod('morph')` | `<meta name="turbo-refresh-method" content="morph">` |
| `@turboRefreshScroll('preserve')` | `<meta name="turbo-refresh-scroll" content="preserve">` |

### Full Controller Example

```php
class MessageController extends Controller
{
    public function store(Request $request)
    {
        $message = Message::create($request->validated());

        if (request()->wantsTurboStream()) {
            return turbo_stream()
                ->append('messages', view('messages.item', compact('message')))
                ->update('message-form', view('messages.form'))
                ->update('message-count', '<span>' . Message::count() . '</span>');
        }

        return redirect()->route('messages.index');
    }

    public function destroy(Message $message)
    {
        $message->delete();

        if (request()->wantsTurboStream()) {
            return turbo_stream()->remove(dom_id($message));
        }

        return redirect()->route('messages.index');
    }

    public function edit(Message $message)
    {
        if (request()->wantsTurboStream() && request()->wasFromTurboFrame('modal')) {
            return turbo_stream()->update('modal-content', view('messages.edit', compact('message')));
        }

        return view('messages.edit', compact('message'));
    }
}
```

## Testing

The package provides testing utilities for asserting Turbo Stream responses.

### Setup

Add the `InteractsWithTurbo` trait to your test class:

```php
use Emaia\LaravelHotwireTurbo\Testing\InteractsWithTurbo;

class MessageControllerTest extends TestCase
{
    use InteractsWithTurbo;
}
```

### Making Turbo Requests

```php
// Send request with Turbo Stream Accept header
$this->turbo()->post('/messages', ['body' => 'Hello']);

// Send request from a specific Turbo Frame
$this->fromTurboFrame('modal')->get('/messages/create');

// Combine both
$this->turbo()->fromTurboFrame('modal')->post('/messages', $data);
```

### Asserting Responses

```php
// Assert the response is a Turbo Stream
$this->turbo()
    ->post('/messages', ['body' => 'Hello'])
    ->assertTurboStream();

// Assert stream count and match specific streams
$this->turbo()
    ->delete("/messages/{$message->id}")
    ->assertTurboStream(fn ($streams) => $streams
        ->has(1)
        ->hasTurboStream(fn ($stream) => $stream
            ->where('action', 'remove')
            ->where('target', dom_id($message))
        )
    );

// Assert content inside a stream
$this->turbo()
    ->post('/messages', ['body' => 'Hello'])
    ->assertTurboStream(fn ($streams) => $streams
        ->hasTurboStream(fn ($stream) => $stream
            ->where('action', 'append')
            ->where('target', 'messages')
            ->see('Hello')
        )
    );

// Assert response is NOT a Turbo Stream
$this->get('/messages')->assertNotTurboStream();
```

## Running Tests

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Emaia](https://github.com/emaia)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
