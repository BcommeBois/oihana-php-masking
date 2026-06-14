# Installation

## Prerequisites

### PHP version

`oihana/php-masking` requires **PHP 8.4 or higher**. The library leans on modern language features:

- **Enums** (PHP 8.1+) — `Masker`, `MaskingMode`.
- **Named arguments** (PHP 8.0+) — passing masker parameters explicitly.
- **Asymmetric visibility and property hooks** (PHP 8.4+) — used by `oihana/php-reflect` (a transitive dependency).

Check:

```bash
php -v
# PHP 8.4.x (cli) (built: ...)
```

If your version is older, upgrade PHP through your package manager (`brew install php@8.4`, `apt install php8.4`, etc.).

### Required PHP extensions

| Extension | Role in `oihana/php-masking` |
|---|---|
| `ext-ctype` | Character-class tests (`ctype_digit`, `ctype_alpha`, `ctype_upper`) used by `maskPhone`, `maskZip`, `maskXifyFront`. Bundled with PHP by default. |

> The maskers use `random_int()` (cryptographically secure), enabled by default — no extension to install.

## Composer installation

> Requires [Composer](https://getcomposer.org/) ≥ 2.0.

```bash
composer require oihana/php-masking
```

This command automatically pulls in `oihana/php-reflect` (see [Dependencies](dependencies.md)).

### Development install

To contribute or run the test suite locally:

```bash
git clone https://github.com/BcommeBois/oihana-php-masking.git
cd oihana-php-masking
composer install
```

## Post-installation verification

Create a `test.php` file at the root of your project:

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use function oihana\masking\maskValue;
use function oihana\masking\maskDocument;

echo maskValue( 'xifyFront' , 'secret' , [ 'unmaskedLength' => 3 ] ) , PHP_EOL ;
// xxxret

print_r( maskDocument(
    [ '_key' => 'a' , 'email' => 'real@example.com' ] ,
    [ [ 'path' => 'email' , 'type' => 'email' ] ]
) ) ;
// Array ( [_key] => a [email] => aZ12.bY34@cX56.invalid )
```

```bash
php test.php
```

If the output matches the shapes above, the `composer.autoload.files` autoload is working and the library is operational.

## Run the test suite (dev install only)

`oihana/php-masking` is covered by [PHPUnit 12](https://phpunit.de/):

```bash
composer test
```

To measure coverage (requires Xdebug or PCOV):

```bash
composer coverage        # text + Clover + HTML under build/coverage/
composer coverage:md     # readable Markdown summary (build/coverage/COVERAGE.md)
```

Configuration lives in `phpunit.xml` at the project root.

## Generate the phpDocumentor reference

```bash
composer doc
```

This command cleans then regenerates `docs/` (HTML output). Not to be confused with **this wiki**, which lives under `wiki/` and is hand-written Markdown in FR/EN.

## What's next?

- [Dependencies](dependencies.md) — what `oihana/php-reflect` provides.
- [The maskers catalogue](../guide/maskers.md) — the 10 maskers.
- [Introduction](introduction.md) — back to the overview.
