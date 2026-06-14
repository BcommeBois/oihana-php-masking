# oihana/php-masking — Data-masking toolkit for PHP

![Language](https://img.shields.io/badge/language-English-blue)

`oihana/php-masking` is a PHP 8.4+ library that **anonymizes and redacts** the fields of your documents. The code is organised as **composable standalone functions** autoloaded via `composer.autoload.files`, with **strongly-typed enums** instead of *magic strings*.

![Oihana PHP Masking](https://raw.githubusercontent.com/BcommeBois/oihana-php-masking/main/assets/images/oihana-php-masking-logo-inline-512x160.png)

## Who this documentation is for

PHP developers who need to **remove or scramble sensitive data** (PII) before it leaves a trusted boundary:

- producing **anonymized database dumps** for staging or local development;
- generating **fixtures** and **test data** that look real but reveal nothing;
- redacting **logs** or **exports** for GDPR / privacy compliance.

## Quick start

```php
use function oihana\masking\maskDocument;

$document =
[
    '_key'  => '42',
    'name'  => 'Jane Doe',
    'email' => 'jane.doe@example.com',
    'phone' => '+33 6 12 34 56 78',
];

$rules =
[
    [ 'path' => 'name'  , 'type' => 'xifyFront' , 'unmaskedLength' => 2 ],
    [ 'path' => 'email' , 'type' => 'email' ],
    [ 'path' => 'phone' , 'type' => 'phone' ],
];

// 3rd argument = attributes to keep. Default: nothing protected (data-store agnostic).
$masked = maskDocument( $document , $rules , [ '_key' ] );
// _key stays '42'; name -> "xxxx xxe"; email -> "aZ12.bY34@cX56.invalid"; phone -> same-shape random.
```

## Table of contents

### Getting started — [`getting-started/`](getting-started/)

- [Introduction](getting-started/introduction.md) — what the library does, the *oihana* philosophy, and why it exists.
- [Installation](getting-started/installation.md) — PHP 8.4+ requirement, the `composer require` command, post-install check.
- [Dependencies](getting-started/dependencies.md) — `oihana/php-reflect` and its role.

### Guide — [`guide/`](guide/)

- [The maskers catalogue](guide/maskers.md) — the 10 maskers (`email`, `phone`, `creditCard`, `datetime`, `decimal`, `integer`, `zip`, `random`, `randomString`, `xifyFront`), their parameters and output shape.
- [Masking a single value](guide/values.md) — the `maskValue()` dispatcher and the per-array-element rule.
- [Masking a document](guide/documents.md) — the `maskDocument()` engine, the **path DSL**, protected attributes, rule precedence.

### Cross-cutting

- [Tests & coverage](testing.md) — run the PHPUnit suite, measure coverage, and the `@codeCoverageIgnore` policy.

## Source code

The library code lives under [`src/oihana/masking/`](../../src/oihana/masking/) — namespace `oihana\masking` (functions) and `oihana\masking\enums` (the `Masker` and `MaskingMode` enums).

## See also

- [Packagist `oihana/php-masking`](https://packagist.org/packages/oihana/php-masking) — the package page.
- [API reference (phpDocumentor)](https://bcommebois.github.io/oihana-php-masking) — function-level generated reference.
- [`oihana/php-arango`](https://github.com/BcommeBois/oihana-php-arango) — the ArangoDB toolkit this engine was extracted from.
