# Introduction

## What `oihana/php-masking` does

`oihana/php-masking` is a **PHP 8.4+ toolkit** that **anonymizes and redacts** the fields of a document. Give it a value (or a whole nested document) and a set of rules, and it returns a copy with the sensitive parts replaced by realistic-but-fake data.

It answers a recurring need: you want to **share or reuse data without leaking personal information** —

- an **anonymized dump** of production to test on staging or locally;
- **fixtures** and **test data** that keep the shape and the statistics of real data without exposing anyone;
- **logs** or **exports** redacted for GDPR / privacy compliance.

The code defines *no monolithic class*: it is a collection of **17 standalone functions**, each in its own file, autoloaded via `composer.autoload.files`, plus two **strongly-typed enums** (`Masker`, `MaskingMode`).

## The *oihana* philosophy

Three principles run through the whole library — and more broadly through the `oihana/*` ecosystem:

1. **Composable functions, no heavy framework.** Every utility is an autoload-friendly PHP function. You call `maskDocument()` or compose `maskValue()` yourself instead of instantiating a `MaskingEngine` and chaining its methods. If you can read a function signature, you can use the library.

2. **Zero *magic strings*.** The masker names (`'email'`, `'xifyFront'`, …) are exposed as constants of the `Masker` enum; the collection-level modes (`'masked'`, `'exclude'`, …) as constants of `MaskingMode`. Renames are *refactor-friendly*, IDE autocomplete works, and a typo is caught instantly.

3. **Anonymization, not reversible encryption.** The maskers replace data with **random** values of the same kind/shape. They are *not* a reversible hash or a cipher — the goal is that the original value cannot be recovered from the output.

## Why a dedicated library

This engine started life inside [`oihana/php-arango`](https://github.com/BcommeBois/oihana-php-arango), to post-process the JSON files of a database dump. It quickly became clear that the masking logic itself is **database-agnostic**: it works on plain PHP arrays, knows nothing about ArangoDB, AQL or any driver.

Extracting it into `oihana/php-masking` means:

- it can be reused **anywhere** — any framework, any data source, any output;
- it carries **a single, light dependency** (`oihana/php-reflect`, for the enum trait);
- `oihana/php-arango` now **consumes** it instead of duplicating it.

## The vocabulary

A handful of terms recur throughout this documentation:

- **Masker** — a single transformation, identified by a name (`email`, `phone`, `creditCard`, …). See the [maskers catalogue](../guide/maskers.md).
- **Rule** — a `{ 'path' => …, 'type' => <masker>, …params }` array that says *which* leaves to mask and *how*.
- **Leaf** — a value that is `null`, a scalar or a JSON array. Objects (associative arrays) are descended into, not masked directly.
- **Path** — the locator inside a rule. The supported forms (exact, name-at-any-depth, wildcard, quoted literal) make up the **path DSL** — see [Masking a document](../guide/documents.md).
- **System attributes** — the top-level identity fields (`_key`, `_id`, `_rev`, `_from`, `_to`) that are **never** masked.

## Audience and prerequisites

This documentation assumes the reader masters **PHP 8.4+** (enums, named arguments) and is comfortable with **Composer** and its `autoload.files` mechanism. No prior knowledge of other `oihana/*` libraries is required.

## What's next?

- [Installation](installation.md) — install the library and verify it works.
- [Dependencies](dependencies.md) — the role of `oihana/php-reflect`.
- [The maskers catalogue](../guide/maskers.md) — start masking.

For the full index, back to the [English TOC](../README.md).
