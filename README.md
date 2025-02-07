# Hotwire Turbo with Laravel!

[![Latest Version on Packagist](https://img.shields.io/packagist/v/emaia/laravel-turbo.svg?style=flat-square)](https://packagist.org/packages/emaia/laravel-turbo)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/emaia/laravel-turbo/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/emaia/laravel-turbo/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/emaia/laravel-turbo/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/emaia/laravel-turbo/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/emaia/laravel-turbo.svg?style=flat-square)](https://packagist.org/packages/emaia/laravel-turbo)

The purpose of this package is to facilitate the use of [Turbo](https://turbo.hotwired.dev/) ([Hotwire](https://hotwired.dev/)) in a Laravel app.

## Installation

You can install the package via composer:

```bash
composer require emaia/laravel-turbo
```

## Usage

```php
/* Some controller method... */
public function update(Request $request)
{

    /* ... */

    if (request()->wasFromTurboFrame('modal')) {

        $streamCollection = new StreamCollection([
            new Stream(
                Action::PREPEND,
                'flash-container',
                view('components.flash-message', [
                    'hasSuccess' => true,
                    'message' => $successMessage,
                ])
            ),
            new Stream(
                Action::UPDATE,
                'modal',
                ''
            ),
        ]);

        return response()->turboStream(
            $streamCollection
        );

    }

    return redirect()->back()->with('success', $successMessage);
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
