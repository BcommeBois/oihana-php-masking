# Oihana PHP Masking OpenSource library - Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

### Fixed

- **CI / Docs**: the `Docs` workflow now self-provisions GitHub Pages
  (`enablement: true` on `actions/configure-pages`), fixing the
  `Get Pages site failed — Not Found` error on deploy.

### Changed

- **CI / Docs**: bumped Pages action versions — `configure-pages` v5→v6,
  `upload-pages-artifact` v3→v5, `deploy-pages` v4→v5 — clearing the
  Node 20 deprecation warning.

## [1.0.0] - 2026-06-14

Initial public release.

A portable, framework-agnostic data-masking engine, extracted from
[`oihana/php-arango`](https://github.com/BcommeBois/oihana-php-arango) into its
own standalone package.

### Added

- **10 composable maskers** under the `oihana\masking` namespace, each as a
  standalone autoloaded function:
  - `maskCreditCard()` — random, Luhn-valid 16-digit number;
  - `maskDatetime()` — random instant in `[begin, end]`, `DATE_FORMAT`-style tokens;
  - `maskDecimal()` — random decimal in `[lower, upper]` with a fixed scale;
  - `maskEmail()` — random `AAAA.BBBB@CCCC.invalid` address (RFC 2606);
  - `maskInteger()` — random integer in `[lower, upper]`;
  - `maskPhone()` — same-shape randomization, digit→digit / letter→letter;
  - `maskRandom()` — type-preserving randomization (string/int/float/bool/null);
  - `maskRandomString()` — string-only random replacement, length kept;
  - `maskXifyFront()` — front of each word replaced by `x`, trailing kept;
  - `maskZip()` — same-shape randomization for zip / postal codes.
- **Document engine**: `maskDocument()`, `maskDocumentNode()`, `maskDocumentList()`
  apply a list of path-based rules to a whole document, descending into nested
  objects and arrays.
- **Value dispatcher**: `maskValue()` applies a single masker to a value (and to
  each element of a JSON array individually).
- **Path DSL** via `resolveMaskingRule()`: leaf name, exact dotted path,
  name-at-any-depth (`.name`), wildcard (`*`) and backtick-quoted literal keys.
- **Identity safety**: `maskDocument()`, `maskDocumentNode()` and
  `maskDocumentList()` accept a `$protectedAttributes` argument — the top-level
  attribute names never masked. It defaults to an empty list and **no field name
  is hardcoded**, so the engine is fully data-store agnostic; the caller supplies
  the identity fields of its own model (e.g. `['_key','_id',…]` for ArangoDB).
- **Enums**: `Masker` (the masker names), `MaskingMode` (the per-collection
  modes: `exclude`, `structure`, `masked`, `full`), `MaskingRule` (the rule keys
  `path`, `type`) and `MaskingOption` (the masker option keys: `begin`, `end`,
  `format`, `lower`, `upper`, `scale`, `default`, `unmaskedLength`, `hash`,
  `seed`) — no *magic strings* across the engine.
- **Helper**: `randomAlphaNumeric()`.
- Full unit-test suite (100% line coverage), CI and phpDocumentor workflows,
  bilingual (FR/EN) wiki.
