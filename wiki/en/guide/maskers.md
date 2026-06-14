# The maskers catalogue

A **masker** is a single transformation, identified by a name. The 10 maskers live in the `oihana\masking` namespace, one function per file, and are also exposed as constants of the [`Masker`](../../../src/oihana/masking/enums/Masker.php) enum.

You rarely call a masker function directly — most of the time you name it in a rule (`'type' => 'email'`) and let [`maskValue()`](values.md) or [`maskDocument()`](documents.md) dispatch to it. But every masker is a public, standalone function you *can* call on its own.

> **All maskers are randomized.** The examples below show the *shape* of a possible output, not a fixed value. The original is never recoverable from the result.

## Overview

| `type` | Function | Replaces with | Key parameters |
|---|---|---|---|
| `creditCard` | `maskCreditCard()` | a random, Luhn-valid 16-digit number (`int`) | — |
| `datetime` | `maskDatetime()` | a random instant in `[begin, end]`, formatted | `begin`, `end`, `format` |
| `decimal` | `maskDecimal()` | a random decimal in `[lower, upper]` (`float`) | `lower`, `upper`, `scale` |
| `email` | `maskEmail()` | a random `AAAA.BBBB@CCCC.invalid` address | — |
| `integer` | `maskInteger()` | a random integer in `[lower, upper]` | `lower`, `upper` |
| `phone` | `maskPhone()` | a same-shape number (digit→digit, letter→letter) | `default` |
| `random` | `maskRandom()` | a random value of the **same type** | — |
| `randomString` | `maskRandomString()` | a random string of the same length (strings only) | — |
| `xifyFront` | `maskXifyFront()` | each word's front replaced by `x` | `unmaskedLength`, `hash`, `seed` |
| `zip` | `maskZip()` | a same-shape postal code | `default` |

## Reference

### `creditCard`

Returns a random 16-digit number whose check digit satisfies the [Luhn algorithm](https://en.wikipedia.org/wiki/Luhn_algorithm). The original value is ignored.

```php
use function oihana\masking\maskCreditCard;

maskCreditCard( '4111-1111-1111-1111' ); // e.g. 4143300214110028 (int)
```

### `datetime`

Picks a random instant in `[begin, end]` and renders it with `format`, using `DATE_FORMAT()`-style tokens: `%yyyy`, `%yy`, `%mm`, `%m`, `%dd`, `%d`, `%hh`, `%h`, `%ii`, `%i`, `%ss`, `%s`, `%fff`, `%%`. When `format` is empty (the default), an **empty string** is returned. Bounds given in the wrong order are swapped; unparseable bounds fall back to the epoch / now.

```php
use function oihana\masking\maskDatetime;

maskDatetime( null , '2019-01-01' , '2019-12-31' , '%yyyy-%mm-%dd' ); // e.g. "2019-06-17"
maskDatetime( null );                                                 // "" (no format)
```

| Parameter | Default | Meaning |
|---|---|---|
| `begin` | `1970-01-01T00:00:00.000` | Earliest instant (ISO 8601). |
| `end` | `''` → now | Latest instant (ISO 8601). |
| `format` | `''` → returns `""` | The output pattern. |

### `decimal`

Replaces the value — **whatever its original type** — with a random decimal in `[lower, upper]`, rounded to `scale` fraction digits. Bounds are inclusive and swapped if reversed; a negative `scale` is clamped to 0.

```php
use function oihana\masking\maskDecimal;

maskDecimal( 3.14 );                 // e.g. -0.42 (default -1..1, scale 2)
maskDecimal( 'x' , -0.3 , 0.3 , 2 ); // e.g. 0.17
```

| Parameter | Default | Meaning |
|---|---|---|
| `lower` | `-1.0` | Smallest value. |
| `upper` | `1.0` | Largest value. |
| `scale` | `2` | Max fraction digits. |

### `email`

Returns a random `AAAA.BBBB@CCCC.invalid` address. The `.invalid` TLD is reserved (RFC 2606) and never resolves. The original value is never reflected.

```php
use function oihana\masking\maskEmail;

maskEmail( 'real.person@example.com' ); // e.g. "x7Bq.9aMz@Kp3R.invalid"
```

### `integer`

Replaces the value — **whatever its original type** — with a random integer in `[lower, upper]` (inclusive, swapped if reversed).

```php
use function oihana\masking\maskInteger;

maskInteger( 9999 );         // e.g. 42  (default -100..100)
maskInteger( 'x' , 0 , 10 ); // e.g. 7
```

| Parameter | Default | Meaning |
|---|---|---|
| `lower` | `-100` | Smallest value. |
| `upper` | `100` | Largest value. |

### `phone`

Replaces each **digit** by a random digit and each **letter** by a random letter (case kept); every other character is left unchanged. Non-string values use the `default` fallback.

```php
use function oihana\masking\maskPhone;

maskPhone( '+31 66-77-88' ); // e.g. "+75 10-79-52"
maskPhone( 1234 );           // "+1234567890" (default, non-string)
```

| Parameter | Default | Meaning |
|---|---|---|
| `default` | `'+1234567890'` | Fallback when the value is not a string. |

### `random`

Replaces a leaf with a random value of the **same kind**: strings → a random string; integers → `[-1000, 1000]`; floats → a decimal in `[-1000, 1000]`; booleans → a random boolean; `null` stays `null`.

```php
use function oihana\masking\maskRandom;

maskRandom( 'hello' ); // e.g. "x7Bqz"
maskRandom( 42 );      // e.g. -738
maskRandom( true );    // e.g. false
maskRandom( null );    // null
```

### `randomString`

Like `random`, but **only strings are modified** — any other type is returned unchanged. The replacement keeps the original length (min 1).

```php
use function oihana\masking\maskRandomString;

maskRandomString( 'My Name' ); // e.g. "x7Bqz9a"
maskRandomString( 1234 );      // 1234 (unchanged)
```

### `xifyFront`

Within each **word** (a run of alphanumeric, `_` or `-` characters), every character except the last `unmaskedLength` ones is replaced by `x`; words no longer than `unmaskedLength` are left untouched. Every other character becomes a blank. Non-string values become the fixed string `"xxxx"`; `null` stays `null`. With `hash = true`, an 8-character hash of the input (salted by `seed`) is appended to reduce collisions.

```php
use function oihana\masking\maskXifyFront;

maskXifyFront( 'This is a test!Do you agree?' ); // "xxis is a xxst Do xou xxxee "
maskXifyFront( 'secret' , 3 );                   // "xxxret"
maskXifyFront( true );                           // "xxxx"
maskXifyFront( null );                           // null
```

| Parameter | Default | Meaning |
|---|---|---|
| `unmaskedLength` | `2` | Trailing characters of each word to keep. |
| `hash` | `false` | Append a short hash. |
| `seed` | `0` | Secret used by the hash. |

### `zip`

Same shape-preserving randomization as `phone`, for zip / postal codes.

```php
use function oihana\masking\maskZip;

maskZip( '50674' );   // e.g. "98146"
maskZip( 'SA34-EA' ); // e.g. "OW91-JI"
maskZip( null );      // "12345" (default)
```

| Parameter | Default | Meaning |
|---|---|---|
| `default` | `'12345'` | Fallback when the value is not a string. |

## What's next?

- [Masking a single value](values.md) — the `maskValue()` dispatcher.
- [Masking a document](documents.md) — apply rules to a whole nested document.
