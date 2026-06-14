# Dependencies

`oihana/php-masking` is deliberately **light**. It declares a single runtime `oihana/*` dependency.

## Runtime dependencies

| Package | Version | Why |
|---|---|---|
| `php` | `>=8.4` | Enums, named arguments, modern type system. |
| `ext-ctype` | `*` | Character-class tests (`ctype_digit`, `ctype_alpha`, `ctype_upper`) in `maskPhone`, `maskZip`, `maskXifyFront`. Bundled with PHP. |
| [`oihana/php-reflect`](https://github.com/BcommeBois/oihana-php-reflect) | `dev-main` | Provides `oihana\reflect\traits\ConstantsTrait`, used by the `Masker` and `MaskingMode` enums to expose `getAll()` and friends. |

That is the **whole** runtime surface. The masking functions themselves rely only on PHP built-ins (`random_int`, `array_map`, `ctype_*`, `gmdate`, `md5`, …).

### Why `oihana/php-reflect`?

The two enums (`Masker`, `MaskingMode`) are plain classes holding `const string` values. They `use ConstantsTrait` so that, for example, `Masker::getAll()` returns the list of valid masker names — which `maskValue()` uses to build a helpful error message when an unknown masker is requested:

```php
throw new InvalidArgumentException(
    sprintf( "Unknown masker '%s'. Valid maskers: %s." , $type , implode( ', ' , Masker::getAll() ) )
);
```

`oihana/php-reflect` pulls in `oihana/php-core` transitively; both are stable `oihana/*` building blocks shared across the ecosystem.

## Development dependencies

| Package | Version | Why |
|---|---|---|
| `phpunit/phpunit` | `^12` | The unit-test suite. |
| `nunomaduro/collision` | `^8.8` | Pretty CLI error reporting during tests. |
| `phpdocumentor/shim` | `^3.8` | Runs phpDocumentor via `composer doc`. |

## Stability

The root `composer.json` sets `"minimum-stability": "dev"` with `"prefer-stable": true`, because the `oihana/*` packages are tracked from their `dev-main` branch. Stable third-party packages (PHPUnit, …) are still resolved to their tagged releases.

## What's next?

- [The maskers catalogue](../guide/maskers.md) — the 10 maskers.
- [Installation](installation.md) — back to the install steps.
