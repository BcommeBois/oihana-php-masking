# Oihana PHP - Masking

![Oihana PHP Masking](https://raw.githubusercontent.com/BcommeBois/oihana-php-masking/main/assets/images/oihana-php-masking-logo-inline-512x160.png)

A lightweight, framework-agnostic PHP toolkit to **anonymize and redact** the fields of your documents.

[![Latest Version](https://img.shields.io/packagist/v/oihana/php-masking.svg?style=flat-square)](https://packagist.org/packages/oihana/php-masking)  
[![Total Downloads](https://img.shields.io/packagist/dt/oihana/php-masking.svg?style=flat-square)](https://packagist.org/packages/oihana/php-masking)  
[![License](https://img.shields.io/packagist/l/oihana/php-masking.svg?style=flat-square)](LICENSE)

## 📚 Documentation

User guides (FR + EN), with narrative explanations, examples and recipes:

| | |
|---|---|
| 🇬🇧 **[English documentation](wiki/en/README.md)** | 🇫🇷 **[Documentation française](wiki/fr/README.md)** |
| Getting started, the masking rules, the maskers catalogue, testing. | Démarrage, les règles de masquage, le catalogue des maskers, tests. |

Auto-generated API reference (phpDocumentor):  
👉 https://bcommebois.github.io/oihana-php-masking

## 🚀 Features

- 🎭 **10 composable maskers** — `email`, `phone`, `creditCard` (Luhn-valid), `datetime`, `decimal`, `integer`, `zip`, `random`, `randomString`, `xifyFront`.
- 🗂️ **Document engine** — apply a list of path-based rules to a whole document, descending into nested objects and arrays.
- 🧭 **Expressive path DSL** — target a leaf by name, exact dotted path, name-at-any-depth (`.name`), wildcard (`*`) or a backtick-quoted literal key.
- 🛡️ **Identity-safe by default** — top-level system attributes (`_key`, `_id`, `_rev`, `_from`, `_to`) are never masked.
- 🧱 **Standalone functions, no framework** — autoloaded via `composer.autoload.files`, strongly-typed enums instead of *magic strings*.
- 🧪 **100% unit-tested**.

💡 Designed to be lightweight, testable and compatible with any PHP 8.4+ project — dumps, fixtures, test data, GDPR/PII redaction.

## 📦 Installation

> **Requires [PHP 8.4+](https://php.net/releases/)**

Install via [Composer](https://getcomposer.org):
```bash
composer require oihana/php-masking
```

## ⚡ Quick start

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use function oihana\masking\maskDocument;

$document =
[
    '_key'  => '42',
    'name'  => 'Jane Doe',
    'email' => 'jane.doe@example.com',
    'phone' => '+33 6 12 34 56 78',
    'profile' =>
    [
        'address' => '221B Baker Street',
        'zip'     => '75001',
    ],
];

$rules =
[
    [ 'path' => 'name'          , 'type' => 'xifyFront' , 'unmaskedLength' => 2 ],
    [ 'path' => 'email'         , 'type' => 'email' ],
    [ 'path' => 'phone'         , 'type' => 'phone' ],
    [ 'path' => 'profile.zip'   , 'type' => 'zip' ],
    [ 'path' => '.address'      , 'type' => 'randomString' ],
];

$masked = maskDocument( $document , $rules );
// _key stays '42'; name -> "xxxx xxe", email -> "aZ12.bY34@cX56.invalid", etc.
```

Need a single value rather than a whole document? Use the dispatcher:

```php
use function oihana\masking\maskValue;

maskValue( 'email' , 'real@example.com' );                       // "x7Bq.9aMz@Kp3R.invalid"
maskValue( 'xifyFront' , 'secret' , [ 'unmaskedLength' => 3 ] ); // "xxxret"
```

## ✅ Tests & coverage

Run the full unit-test suite (PHPUnit, strict mode):
```bash
composer test
```

Run a single test case:
```bash
./vendor/bin/phpunit --filter MaskingsTest
```

Measure coverage (requires Xdebug or PCOV):
```bash
composer coverage        # text + Clover + HTML under build/coverage/
composer coverage:md     # readable Markdown summary (build/coverage/COVERAGE.md)
```

The suite covers **100% of lines**. For the testing philosophy and the
`@codeCoverageIgnore` policy, see the detailed guide:
**[wiki/en/testing.md](wiki/en/testing.md)** · **[wiki/fr/testing.md](wiki/fr/testing.md)**.

## 🧾 License

This project is licensed under the [Mozilla Public License 2.0 (MPL-2.0)](https://www.mozilla.org/en-US/MPL/2.0/).

## 👤 About the author

* Author : Marc ALCARAZ (aka eKameleon)
* Mail : marc@ooop.fr
* Website : http://www.ooop.fr

## 🛠️ Generate the documentation

We use [phpDocumentor](https://phpdoc.org/) to generate the API reference into the `./docs` folder:

```bash
composer doc
```

## 🔗 Related packages

- `oihana/php-reflect` – reflection and hydration utilities (provides the `ConstantsTrait` used by the enums): `https://github.com/BcommeBois/oihana-php-reflect`
- `oihana/php-arango` – ArangoDB toolkit; the original home of this masking engine, now consuming `oihana/php-masking`: `https://github.com/BcommeBois/oihana-php-arango`
