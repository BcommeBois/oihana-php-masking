<?php
/**
 * Convert a PHPUnit Clover coverage report into a readable Markdown summary.
 *
 * Usage:
 *   php tools/clover-to-markdown.php [clover.xml] [out.md] [strip-prefix]
 *
 * Defaults:
 *   clover.xml   -> build/coverage/clover.xml
 *   out.md       -> build/coverage/COVERAGE.md
 *   strip-prefix -> auto-detected (longest common source path, e.g. src/oihana/<lib>)
 *
 * Portable across the oihana/php-* libraries: the directory grouping strips the
 * longest path prefix shared by every covered file, so no per-project edit is
 * needed. Pass an explicit prefix as the 3rd argument to override the guess.
 *
 * The output is intentionally NOT committed (build/ is gitignored): a coverage
 * snapshot goes stale at the very next commit. Regenerate it on demand with
 * `composer coverage:md`.
 */

$cloverPath   = $argv[1] ?? 'build/coverage/clover.xml';
$outPath      = $argv[2] ?? 'build/coverage/COVERAGE.md';
$stripPrefix  = $argv[3] ?? null;

if ( !is_file( $cloverPath ) )
{
    fwrite( STDERR, "Clover report not found: {$cloverPath}\n" );
    fwrite( STDERR, "Run `composer coverage` first to generate it.\n" );
    exit( 1 );
}

$xml  = simplexml_load_file( $cloverPath );
$root = realpath( getcwd() ) . '/';

// --- Per-file metrics -------------------------------------------------------

$files = [];

foreach ( $xml->xpath( '//file' ) as $f )
{
    $rel = str_replace( $root , '' , (string) $f['name'] );
    $m   = $f->metrics;

    $st  = (int) $m['statements'];
    $cst = (int) $m['coveredstatements'];

    $files[] = [
        'rel' => $rel ,
        'st'  => $st ,
        'cst' => $cst ,
        'me'  => (int) $m['methods'] ,
        'cme' => (int) $m['coveredmethods'] ,
        'pct' => $st ? $cst / $st * 100 : 100.0 ,
    ];
}

// --- Common source prefix (auto-detected, overridable) ----------------------
//
// The longest leading path shared by every covered file's directory. Stripping
// it keeps the per-directory table readable on any layout without a hardcoded
// project path. Degrades gracefully to '' when files share no common root.

if ( $stripPrefix === null )
{
    $stripPrefix = '';

    $dirs0 = array_values( array_map( static fn( $f ) => dirname( $f['rel'] ) , $files ) );

    if ( $dirs0 )
    {
        $common = explode( '/' , $dirs0[0] );

        foreach ( $dirs0 as $d )
        {
            $segs = explode( '/' , $d );
            $i    = 0;

            while ( $i < count( $common ) && isset( $segs[ $i ] ) && $common[ $i ] === $segs[ $i ] )
            {
                $i++;
            }

            $common = array_slice( $common , 0 , $i );

            if ( !$common ) { break; }
        }

        $stripPrefix = implode( '/' , $common );
    }
}

// --- Group by directory under the common source prefix ----------------------

$dirs = [];

foreach ( $files as $f )
{
    $d = dirname( $f['rel'] );

    if ( $stripPrefix !== '' )
    {
        $d = preg_replace( '#^' . preg_quote( $stripPrefix , '#' ) . '/?#' , '' , $d );
    }

    if ( $d === '' || $d === '.' ) { $d = '(root)'; }

    $dirs[ $d ]['st']  = ( $dirs[ $d ]['st']  ?? 0 ) + $f['st'];
    $dirs[ $d ]['cst'] = ( $dirs[ $d ]['cst'] ?? 0 ) + $f['cst'];
    $dirs[ $d ]['me']  = ( $dirs[ $d ]['me']  ?? 0 ) + $f['me'];
    $dirs[ $d ]['cme'] = ( $dirs[ $d ]['cme'] ?? 0 ) + $f['cme'];
}

// --- Project totals ---------------------------------------------------------

$pr   = $xml->xpath( '//project/metrics' )[0];
$tSt  = (int) $pr['statements'];
$tCst = (int) $pr['coveredstatements'];
$tMe  = (int) $pr['methods'];
$tCme = (int) $pr['coveredmethods'];

// Class totals (PHPUnit-style): Clover's project metrics carry no covered-class
// count, so derive it from the per-class metrics. A class enters the tally only
// once it has executable code — interfaces, enums and constant-only classes have
// zero statements and are skipped, matching PHPUnit's `classes` figure — and
// counts as covered when every one of its statements ran.

$tCls  = 0;
$tCCls = 0;

foreach ( $xml->xpath( '//class' ) as $c )
{
    $cst = (int) $c->metrics['statements'];

    if ( $cst === 0 ) { continue; }

    $tCls++;

    if ( (int) $c->metrics['coveredstatements'] === $cst ) { $tCCls++; }
}

$linePct  = $tSt  ? $tCst  / $tSt  * 100 : 100.0;
$methPct  = $tMe  ? $tCme  / $tMe  * 100 : 100.0;
$classPct = $tCls ? $tCCls / $tCls * 100 : 100.0;

$bar = static fn( float $p ): string =>
    str_repeat( '#' , (int) round( $p / 10 ) ) . str_repeat( '.' , 10 - (int) round( $p / 10 ) );

// --- History: load previous snapshot, compute deltas ------------------------
//
// A small JSON log (gitignored alongside the report) records each run's totals
// and an explicit timestamp. We compare against the *previous* run rather than
// trusting the report file's mtime, which is unreliable (touch, checkout, no-op
// regeneration) and disappears with `build/`.

$historyPath = dirname( $outPath ) . '/history.json';
$history     = is_file( $historyPath )
    ? ( json_decode( (string) file_get_contents( $historyPath ) , true ) ?: [] )
    : [];

$prev = $history ? end( $history ) : null;
$now  = date( 'Y-m-d H:i:s' );

$delta = static function ( float $cur , ?float $old , int $curN , ?int $oldN , string $unit ): string
{
    if ( $old === null ) { return '' ; }

    $d  = $cur - $old;
    $dn = $oldN === null ? 0 : $curN - $oldN;

    $arrow = abs( $d ) < 0.005 ? '=' : ( $d > 0 ? '▲' : '▼' );
    $pts   = abs( $d ) < 0.005 ? '±0.00 pts' : sprintf( '%+.2f pts' , $d );

    return sprintf( '%s %s (%+d %s)' , $arrow , $pts , $dn , $unit );
};

$lineDelta  = $delta( $linePct  , $prev['lines']['pct']   ?? null , $tCst  , $prev['lines']['cst']    ?? null , 'lines' );
$methDelta  = $delta( $methPct  , $prev['methods']['pct'] ?? null , $tCme  , $prev['methods']['cme']  ?? null , 'methods' );
$classDelta = $delta( $classPct , $prev['classes']['pct'] ?? null , $tCCls , $prev['classes']['ccls'] ?? null , 'classes' );

// --- Render -----------------------------------------------------------------

$o   = [];
$o[] = '# Test coverage report';
$o[] = '';
$o[] = '> Generated by `composer coverage:md` (PHPUnit Clover -> Markdown).';
$o[] = '> Not committed: `build/` is gitignored. Regenerate on demand.';
$o[] = '';
$o[] = sprintf( '> Generated at **%s**.%s' , $now ,
    $prev ? sprintf( ' Compared against the previous run of **%s**.' , $prev['ts'] ) : ' First recorded run — no previous data to compare.' );
$o[] = '';
$o[] = '## Summary';
$o[] = '';
$o[] = '| Metric | Coverage | Δ since last run | |';
$o[] = '|---|---|---|---|';
$o[] = sprintf( '| **Lines** | %.2f%% (%d/%d) | %s | `%s` |'   , $linePct  , $tCst  , $tSt  , $lineDelta  ?: '—' , $bar( $linePct ) );
$o[] = sprintf( '| **Methods** | %.2f%% (%d/%d) | %s | `%s` |' , $methPct  , $tCme  , $tMe  , $methDelta  ?: '—' , $bar( $methPct ) );
$o[] = sprintf( '| **Classes** | %.2f%% (%d/%d) | %s | `%s` |' , $classPct , $tCCls , $tCls , $classDelta ?: '—' , $bar( $classPct ) );
$o[] = '';
$o[] = '## Coverage by directory (lines)';
$o[] = '';
$o[] = '| Directory | Lines | Methods | |';
$o[] = '|---|---|---|---|';

uasort( $dirs , static fn( $a , $b ) =>
    ( $a['st'] ? $a['cst'] / $a['st'] : 1 ) <=> ( $b['st'] ? $b['cst'] / $b['st'] : 1 ) );

foreach ( $dirs as $d => $v )
{
    if ( !$v['st'] ) { continue; }

    $p  = $v['cst'] / $v['st'] * 100;
    $mp = $v['me'] ? $v['cme'] / $v['me'] * 100 : 100;

    $o[] = sprintf( '| `%s` | %.1f%% (%d/%d) | %.1f%% (%d/%d) | `%s` |' ,
        $d , $p , $v['cst'] , $v['st'] , $mp , $v['cme'] , $v['me'] , $bar( $p ) );
}

$o[] = '';
$o[] = '## 20 least-covered files (priority targets)';
$o[] = '';
$o[] = '| File | Lines | Methods |';
$o[] = '|---|---|---|';

usort( $files , static fn( $a , $b ) => [ $a['pct'] , $a['st'] ] <=> [ $b['pct'] , $b['st'] ] );

$n = 0;

foreach ( $files as $f )
{
    if ( $f['st'] === 0 || $f['pct'] >= 100 ) { continue; }

    $o[] = sprintf( '| `%s` | %.1f%% (%d/%d) | %d/%d |' ,
        $f['rel'] , $f['pct'] , $f['cst'] , $f['st'] , $f['cme'] , $f['me'] );

    if ( ++$n >= 20 ) { break; }
}

$o[] = '';
$o[] = '## Files at 0% (never touched by a test)';
$o[] = '';

$zero = array_filter( $files , static fn( $f ) => $f['st'] > 0 && $f['cst'] === 0 );

if ( !$zero )
{
    $o[] = '_None — every file with executable code is touched at least once._';
}
else
{
    foreach ( $zero as $f )
    {
        $o[] = sprintf( '- `%s` (%d statements)' , $f['rel'] , $f['st'] );
    }
}

$o[] = '';

$dir = dirname( $outPath );
if ( !is_dir( $dir ) ) { mkdir( $dir , 0o775 , true ); }

file_put_contents( $outPath , implode( "\n" , $o ) . "\n" );

// --- Append this run to the history log -------------------------------------

$history[] = [
    'ts'      => $now ,
    'lines'   => [ 'cst'  => $tCst  , 'st'  => $tSt  , 'pct' => round( $linePct  , 2 ) ] ,
    'methods' => [ 'cme'  => $tCme  , 'me'  => $tMe  , 'pct' => round( $methPct  , 2 ) ] ,
    'classes' => [ 'ccls' => $tCCls , 'cls' => $tCls , 'pct' => round( $classPct , 2 ) ] ,
];

// Keep the log bounded — only the last 50 runs are useful.
if ( count( $history ) > 50 ) { $history = array_slice( $history , -50 ); }

file_put_contents( $historyPath , json_encode( $history , JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . "\n" );

echo "Coverage Markdown written to {$outPath}\n";
