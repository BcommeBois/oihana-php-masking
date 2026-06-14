# Masking a document

[`maskDocument()`](../../../src/oihana/masking/maskDocument.php) is the engine. It takes a **document** (a decoded JSON object, i.e. an associative array) and a list of **rules**, and returns a masked copy.

```php
use function oihana\masking\maskDocument;

maskDocument( array $doc , array $maskings , array $protectedAttributes = [] ) : array
```

- `$doc` — the document to mask;
- `$maskings` — the list of rules (see below);
- `$protectedAttributes` — top-level attribute names that must **never** be masked (default: none). See [Protected attributes](#protected-attributes).

Each rule is an array:

```php
[ 'path' => <path> , 'type' => <masker> , ...params ]
```

- `path` — *which* leaves to mask (see the [path DSL](#the-path-dsl) below);
- `type` — *how* to mask them (a masker name; see the [catalogue](maskers.md));
- any extra keys are passed to the masker as parameters (`unmaskedLength`, `lower`, `format`, …).

> When you build rules in PHP rather than load them from TOML/JSON, the keys are also available as constants — `MaskingRule::PATH` / `MaskingRule::TYPE` and the option keys on `MaskingOption` (`UNMASKED_LENGTH`, `LOWER`, `FORMAT`, …) — so you can avoid *magic strings*.

## A first example

```php
use function oihana\masking\maskDocument;

$doc =
[
    '_key'    => 'a',
    'email'   => 'real@example.com',
    'profile' => [ 'name' => 'Jane' ],
];

$out = maskDocument( $doc,
[
    [ 'path' => 'email' , 'type' => 'email' ],
    [ 'path' => '.name' , 'type' => 'xifyFront' ],
]);
// [ '_key' => 'a' , 'email' => 'aZ12.bY34@cX56.invalid' , 'profile' => [ 'name' => 'xxne' ] ]
```

An **empty rule list** returns the document untouched.

## How the engine walks the document

- A **leaf** — a value that is `null`, a scalar or a JSON array (list) — is a candidate for masking.
- An **object** (associative array) is **descended into**, never masked as a whole.
- When a matched leaf is itself an array, the masker is applied to its elements individually (see [Masking a single value](values.md#arrays-are-masked-element-by-element)).
- An array leaf with **no matching rule** is walked deeper, so a rule can still match objects nested inside it.

## The path DSL

The `path` of a rule selects the leaves it applies to. Five forms are supported:

| Form | Matches | Example |
|---|---|---|
| `"name"` | a leaf attribute `name` at the **top level** | `'email'` |
| `"a.b"` | the **exact** nested path `a` → `b` (through objects only) | `'profile.zip'` |
| `".name"` | **every** leaf named `name`, at **any depth** | `'.address'` |
| `"*"` | **every** leaf | `'*'` |
| `` "`a.b`" `` | a **literal** attribute name containing dots (backtick/tick quoted) | `` '`user.id`' `` |

### Exact path

```php
$doc = [ 'person' => [ 'name' => 'foobar' ] , 'other' => [ 'name' => 'kepterm' ] ];
maskDocument( $doc , [ [ 'path' => 'person.name' , 'type' => 'xifyFront' , 'unmaskedLength' => 2 ] ]);
// person.name -> "xxxxar"; other.name stays "kepterm" (the exact path does not match elsewhere)
```

### Name at any depth (`.name`)

```php
$doc = [ 'name' => 'top' , 'nicknames' => [ [ 'name' => 'hugo' ] , 'egon' ] ];
maskDocument( $doc , [ [ 'path' => '.name' , 'type' => 'xifyFront' , 'unmaskedLength' => 2 ] ]);
// every "name" leaf is masked, including the one nested inside the array;
// the bare array scalar "egon" is left alone (it is not a keyed attribute).
```

### Wildcard (`*`)

```php
$doc = [ '_key' => 'k' , 'n' => 5 , 'arr' => [ 1 , 2 ] , 'o' => [ 'x' => 9 ] ];
maskDocument( $doc , [ [ 'path' => '*' , 'type' => 'integer' , 'lower' => 0 , 'upper' => 0 ] ] , [ '_key' ] );
// every leaf becomes 0 — except _key, which is listed in the protected attributes.
// (without that third argument, _key would be masked too — nothing is protected by default.)
```

### Backtick-quoted literal key

When an attribute name itself contains a dot, quote it so the engine does not read it as a nested path:

```php
$doc = [ 'a.b' => 'topsecret' , 'plain' => 'keepme' ];
maskDocument( $doc , [ [ 'path' => '`a.b`' , 'type' => 'xifyFront' , 'unmaskedLength' => 2 ] ]);
// the "a.b" leaf is masked; "plain" is not.
```

## Rule precedence — first match wins

Rules are evaluated **in declaration order**; the **first** one that matches a leaf is applied. List the more specific rule before the broader one:

```php
$doc = [ 'address' => 'topsecret' ];
maskDocument( $doc,
[
    [ 'path' => 'address'  , 'type' => 'xifyFront' , 'unmaskedLength' => 2 ], // wins
    [ 'path' => '.address' , 'type' => 'email' ],                            // never reached for this leaf
]);
// address -> "xxxxxxxet" (xifyFront), not an email.
```

## Protected attributes

The third argument, `$protectedAttributes`, lists the top-level attribute names that must **never** be masked — not even by a `*` rule. Use it to preserve identity fields (a primary key, a tenant id, …).

**The engine is data-store agnostic: by default nothing is protected** (`$protectedAttributes = []`), and **no field name is hardcoded**. You supply the identity fields of your own model:

```php
use function oihana\masking\maskDocument;

// ArangoDB-style document store:
maskDocument( $doc , $rules , [ '_key' , '_id' , '_rev' , '_from' , '_to' ] );

// MongoDB — protect _id only:
maskDocument( $doc , $rules , [ '_id' ] );

// Your own model — protect whatever identifies a record:
maskDocument( $doc , $rules , [ 'id' , 'uuid' , 'tenant' ] );
```

> Define that list once as a constant in your own project and reuse it, so the field names live in a single place.

> Protection applies at the **top level only** — a nested attribute that happens to share a protected name *is* eligible for masking.

## A rule without a `type` throws

Every rule must name a masker. A rule whose `type` is missing (or not a string) raises an `InvalidArgumentException`:

```php
maskDocument( [ 'email' => 'x' ] , [ [ 'path' => 'email' ] ] );
// InvalidArgumentException: Masking rule for path 'email' has no type.
```

## The lower-level helpers

`maskDocument()` is built on three public helpers you can also call directly when you need finer control:

- [`maskDocumentNode()`](../../../src/oihana/masking/maskDocumentNode.php) — walk one object, masking matching leaves and recursing into nested objects/arrays. Signature: `maskDocumentNode( array $node , array $maskings , ?string $exactPath , int $depth )`.
- [`maskDocumentList()`](../../../src/oihana/masking/maskDocumentList.php) — apply rules to every element of a list.
- [`resolveMaskingRule()`](../../../src/oihana/masking/resolveMaskingRule.php) — return the first rule matching a given attribute name and exact path, or `null`.

```php
use function oihana\masking\resolveMaskingRule;

$rules = [ [ 'path' => 'person.name' , 'type' => 'xifyFront' ] ];
resolveMaskingRule( $rules , 'name' , 'person.name' ); // the rule
resolveMaskingRule( $rules , 'name' , 'other.name' );  // null
```

## What's next?

- [The maskers catalogue](maskers.md) — every masker and its parameters.
- [Masking a single value](values.md) — the `maskValue()` dispatcher.
- [Tests & coverage](../testing.md) — how the engine is verified.
