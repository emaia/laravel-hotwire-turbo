# Changelog

All notable changes to `laravel-hotwire-turbo` will be documented in this file.

## 0.8.5 - 2026-04-13

### Added

- **Turbo Drive redirect 303** — New `TurboMiddleware` automatically converts redirects to HTTP 303 (See Other) for Turbo visits, which is required by Turbo Drive for form submission redirects. Enabled by default via `auto_redirect_303` config option. Can be disabled and registered manually on specific routes.

## 0.8.4 - 2026-04-13

**Full Changelog**: https://github.com/emaia/laravel-hotwire-turbo/compare/v0.8.3...v0.8.4

## v0.8.0 - 2026-04-12

### Breaking Changes

- Removed `Action::MORPH` enum case, `Stream::morph()` and `turbo_stream()->morph()` — `morph` was never a valid Turbo Stream action. Use `method: 'morph'` on `replace()`, `update()`, or `refresh()` instead, per the [official spec](https://turbo.hotwired.dev/reference/streams).

### Added

- **Model-aware targets** — All action methods now accept Eloquent models (or any object with `getKey()`) in addition to strings. The target is resolved automatically via `dom_id()`.
- **CSS selector targeting** — New `*All()` methods (`appendAll`, `prependAll`, `replaceAll`, `updateAll`, `removeAll`, `afterAll`, `beforeAll`) to target multiple elements via CSS selectors using Turbo's `targets` attribute.
- **Conditional chaining** — `when()` and `unless()` on `TurboStreamBuilder` via Laravel's `Conditionable` trait.
- **Publishable config** — `config/turbo.php` with `model_namespaces` for custom model directory structures.
- **`refresh()` parameters** — `method`, `scroll`, and `requestId` named arguments for `refresh()`.
- **`replaceAll`/`updateAll` with morph** — `method: 'morph'` support on CSS selector variants.

### Fixed

- `turbo_stream_view()` now returns `Emaia\LaravelHotwireTurbo\Response` instead of `Illuminate\Http\Response`.

### Migration Guide

```php
// Before (v0.7)
turbo_stream()->morph('card', $content);

// After (v0.8)
turbo_stream()->replace('card', $content, method: 'morph');  // morph entire element
turbo_stream()->update('card', $content, method: 'morph');   // morph children only

```
## v0.7.0 - 2026-04-11

**Full Changelog**: https://github.com/emaia/laravel-hotwire-turbo/compare/v0.6.0...v0.7.0
