# Hotwire Turbo with Laravel!

[![Latest Version on Packagist](https://img.shields.io/packagist/v/emaia/laravel-hotwire-turbo.svg?style=flat-square)](https://packagist.org/packages/emaia/laravel-hotwire-turbo)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/emaia/laravel-hotwire-turbo/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/emaia/laravel-hotwire-turbo/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/emaia/laravel-hotwire-turbo/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/emaia/laravel-hotwire-turbo/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/emaia/laravel-hotwire-turbo.svg?style=flat-square)](https://packagist.org/packages/emaia/laravel-hotwire-turbo)

The purpose of this package is to facilitate the use of [Turbo](https://turbo.hotwired.dev/) ([Hotwire](https://hotwired.dev/)) in a Laravel app.

## Installation

You can install the package via composer:

```bash
composer require emaia/laravel-hotwire-turbo
```

## Usage

### Turbo Stream Actions

The package supports all Turbo Stream actions via the `Action` enum:

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

The `turbo_stream()` helper provides a chainable API with zero imports needed:

```php
return turbo_stream()
    ->append('messages', view('messages.item', compact('message')))
    ->remove('modal')
    ->update('counter', '<span>42</span>')
    ->respond();
```

Pass a custom status code if needed:

```php
return turbo_stream()
    ->replace('form', view('form', ['errors' => $errors]))
    ->respond(422);
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

Or use the constructor directly with the `Action` enum:

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

Use `targets` to target multiple DOM elements via CSS selector instead of a single ID:

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

### Returning Turbo Stream Responses

The package adds a `turboStream` macro to Laravel's response factory. It automatically sets the `Content-Type: text/vnd.turbo-stream.html` header:

```php
// Single stream
return response()->turboStream(
    Stream::replace('todo-item-1', view('todos.item', ['todo' => $todo]))
);

// Multiple streams
return response()->turboStream($streamCollection);

// With custom status code
return response()->turboStream($stream, 201);
```

### Detecting Turbo Requests

The package adds macros to Laravel's `Request` to detect Turbo-specific headers:

```php
// Check if the request accepts Turbo Stream responses
if (request()->wantsTurboStream()) {
    return turbo_stream()
        ->replace('todo-1', view('todos.item', ['todo' => $todo]))
        ->respond();
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

Extend `TurboFormRequest` instead of `FormRequest` to handle validation errors correctly within Turbo Frames. When validation fails inside a Turbo Frame, it redirects to the previous URL so the frame can re-render with errors:

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

Use the `<x-turbo::stream>` component directly in your views:

```blade
<x-turbo::stream action="append" target="messages">
    <div class="message">
        {{ $message->body }}
    </div>
</x-turbo::stream>

<x-turbo::stream action="remove" target="notification-{{ $id }}" />

{{-- Target multiple elements with CSS selector --}}
<x-turbo::stream action="update" targets=".unread-badge">
    <span>0</span>
</x-turbo::stream>
```

#### Turbo Frame

Use the `<x-turbo::frame>` component to create Turbo Frames:

```blade
{{-- Basic frame --}}
<x-turbo::frame id="user-profile">
    @include('users.profile', ['user' => $user])
</x-turbo::frame>

{{-- Lazy-loaded frame --}}
<x-turbo::frame id="comments" src="/posts/{{ $post->id }}/comments" loading="lazy">
    <p>Loading comments...</p>
</x-turbo::frame>

{{-- Frame that navigates the whole page --}}
<x-turbo::frame id="navigation" target="_top">
    <a href="/dashboard">Dashboard</a>
</x-turbo::frame>

{{-- Disabled frame (no navigation) --}}
<x-turbo::frame id="preview" :disabled="true">
    <p>This frame won't navigate.</p>
</x-turbo::frame>
```

### Turbo Drive Blade Directives

Control Turbo Drive behavior with Blade directives in your layout's `<head>`:

```blade
<head>
    {{-- Exclude page from Turbo's cache --}}
    @turboNocache

    {{-- Don't show cached preview on revisit --}}
    @turboNoPreview

    {{-- Use morphing for page refreshes (Turbo 8) --}}
    @turboRefreshMethod('morph')

    {{-- Preserve scroll position on refresh --}}
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
                ->update('message-count', '<span>' . Message::count() . '</span>')
                ->respond();
        }

        return redirect()->route('messages.index');
    }

    public function destroy(Message $message)
    {
        $message->delete();

        if (request()->wantsTurboStream()) {
            return turbo_stream()
                ->remove("message-{$message->id}")
                ->respond();
        }

        return redirect()->route('messages.index');
    }
}
```

## Testing

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
