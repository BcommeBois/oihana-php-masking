# Tests & coverage

`oihana/php-masking` is covered by [PHPUnit 12](https://phpunit.de/), running in **strict mode**.

## Running the suite

```bash
composer test            # run the unit suite
./vendor/bin/phpunit --filter MaskingsTest   # a single test case
```

## Measuring coverage

Coverage requires **Xdebug** or **PCOV**.

```bash
composer coverage        # text + Clover + HTML under build/coverage/
composer coverage:md     # readable Markdown summary (build/coverage/COVERAGE.md)
```

`composer coverage:md` converts the Clover report into `build/coverage/COVERAGE.md` and keeps a small local trend log (`build/coverage/history.json`) so each run shows the delta since the previous one.

The whole library is at **100% line coverage**.

> `build/coverage/` is **gitignored** — a coverage snapshot goes stale at the very next commit, so we regenerate it on demand rather than committing it.

## Strict mode

`phpunit.xml` fails the run on warnings, risky tests (no assertion) and skipped tests:

```
failOnRisky="true"  failOnWarning="true"  failOnSkipped="true"  failOnIncomplete="true"
```

A test that checks nothing protects nothing.

## Testing randomized code

Every masker is **randomized** — the same input yields a different output each run. So the tests assert on the **shape** and **invariants** of the result, never on a fixed value:

- **length** — `maskPhone('+31 6A-77')` keeps the same length and the non-alnum characters in place;
- **character classes** — a digit stays a digit, an uppercase letter stays uppercase;
- **bounds** — `maskInteger(…, 0, 10)` lands in `[0, 10]`;
- **invariants** — `maskCreditCard()` always satisfies the Luhn checksum;
- **anonymization** — `maskEmail('real.person@…')` never contains the original local part.

A few maskers are deterministic enough to pin exactly — e.g. `maskXifyFront('This is a test!Do you agree?')` is asserted against its exact reference output.

## The `@codeCoverageIgnore` policy

Test everything reachable. Only annotate a line `@codeCoverageIgnore` when it is genuinely impossible to reach from the public surface (a defensive guard that no input can trigger). The library currently needs **none**.

## A note on behaviour stability

When you discover a surprising behaviour in existing code, **freeze it in a test** first. Do not change a public function's behaviour without discussing it: other libraries — notably [`oihana/php-arango`](https://github.com/BcommeBois/oihana-php-arango), which consumes this engine — may rely on it.

## What's next?

- [Masking a document](guide/documents.md) — the engine these tests cover.
- Back to the [English TOC](README.md).
