# Masking a single value

[`maskValue()`](../../../src/oihana/masking/maskValue.php) is the **dispatcher** in front of the per-type maskers. It is what `maskDocument()` calls under the hood, and you can use it directly when you have a value in hand rather than a whole document.

```php
use function oihana\masking\maskValue;

maskValue( string $type , mixed $value , array $params = [] ) : mixed
```

- `$type` — a masker name (see the [catalogue](maskers.md), or the `Masker` enum constants);
- `$value` — the value to mask;
- `$params` — the masker parameters (the same keys documented in the catalogue).

## Examples

```php
use function oihana\masking\maskValue;

maskValue( 'email' , 'real@example.com' );                       // "x7Bq.9aMz@Kp3R.invalid"
maskValue( 'xifyFront' , 'secret' , [ 'unmaskedLength' => 3 ] ); // "xxxret"
maskValue( 'integer' , 'x' , [ 'lower' => 0 , 'upper' => 10 ] ); // e.g. 7
maskValue( 'phone' , 1 );                                        // "+1234567890" (non-string fallback)
```

Using the enum to avoid *magic strings*:

```php
use oihana\masking\enums\Masker;
use function oihana\masking\maskValue;

maskValue( Masker::CREDIT_CARD , null ); // e.g. 4143300214110028
```

## Arrays are masked element by element

When `$value` is a **JSON array** (a PHP *list*), the masker is applied to **each element individually**:

- a scalar element is masked directly;
- a nested **list** recurses;
- a nested **object** (associative array) is left untouched — a value-level masker does not descend into objects.

```php
use function oihana\masking\maskValue;

$out = maskValue( 'integer' , [ 1 , [ 2 , 3 ] , [ 'o' => 'keep' ] , 'str' ] , [ 'lower' => 0 , 'upper' => 0 ] );
// [ 0 , [ 0 , 0 ] , [ 'o' => 'keep' ] , 0 ]
//   ↑    ↑           ↑                   ↑
//   |    sub-list    nested object       scalar masked
//   scalar masked    untouched
```

This mirrors the common "array elements are masked individually" rule, so masking a list of phone numbers or a list of tags does the right thing without extra configuration.

## Unknown maskers throw

Passing a name that is not a known masker raises an `InvalidArgumentException` listing the valid names:

```php
maskValue( 'nope' , 'x' );
// InvalidArgumentException:
// Unknown masker 'nope'. Valid maskers: creditCard, datetime, decimal, email, integer, phone, random, randomString, xifyFront, zip.
```

## When to use `maskValue` vs `maskDocument`

| Use… | When… |
|---|---|
| `maskValue()` | you already have the exact value(s) to mask and decide *which* masker yourself. |
| [`maskDocument()`](documents.md) | you have a whole document and want **path-based rules** to select the leaves, descend into nested objects/arrays, and protect the attributes you choose. |

## What's next?

- [Masking a document](documents.md) — the path DSL and the document engine.
- [The maskers catalogue](maskers.md) — every masker and its parameters.
