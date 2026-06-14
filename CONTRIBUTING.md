# Contributing

Thanks for helping improve **oihana/php-masking**.

## Requirements

- **PHP 8.4+**
- **Composer**
- **Xdebug** or **PCOV** — only needed to measure test coverage (see below).

## Setup

```shell
git clone https://github.com/BcommeBois/oihana-php-masking.git
cd oihana-php-masking
composer install
```

## Tests & coverage

```shell
composer test            # run the unit suite (PHPUnit, strict mode)
composer coverage        # suite + coverage report (text + Clover + HTML under build/coverage/)
composer coverage:md     # regenerate build/coverage/COVERAGE.md, a readable Markdown summary
```

For the full guide — testability philosophy, the characterization rule, reading
the report and the `@codeCoverageIgnore` policy — see
[wiki/en/testing.md](wiki/en/testing.md) (FR: [wiki/fr/testing.md](wiki/fr/testing.md)).

The suite runs in **strict mode**: warnings, risky tests (no assertion) and
skipped tests all fail the run. A test that checks nothing protects nothing.

Coverage output lives under `build/coverage/` and is **gitignored** — it is a
snapshot that goes stale at the next commit, so we regenerate it on demand
rather than committing it. `composer coverage:md` also keeps a small local
trend log (`build/coverage/history.json`) so each run shows the delta since the
previous one.

A short reminder of the testing philosophy:

- Coverage measures which lines ran, **not** which behaviours are verified —
  100% coverage is not zero bugs.
- The maskers are **randomized**: assert on the *shape* and *invariants* of the
  output (length, character classes, bounds, the Luhn checksum) rather than on a
  fixed value.
- When you discover a surprising behaviour in existing code, **freeze it in a
  test** first. Do not change a public API's behaviour without discussing it:
  other libraries (e.g. `oihana/php-arango`) may rely on it.
- Test everything reachable; only annotate a line `@codeCoverageIgnore` when it
  is genuinely impossible to reach.

## Coding conventions

- One function per file, autoloaded via `composer.autoload.files`.
- Strongly-typed enums instead of *magic strings*.
- Inside a class/trait, members are ordered: constructor → used traits →
  constants → properties → public → protected → private, each group sorted
  alphabetically.
