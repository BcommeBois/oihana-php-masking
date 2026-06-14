<?php

namespace oihana\masking;

use Random\RandomException;

/**
 * Replaces a value with a random date/time between two bounds, formatted.
 *
 * Implements the conventional `datetime` masker: a random instant is picked in
 * `[begin, end]` and rendered with `format`. The format uses `DATE_FORMAT()`-style
 * tokens — the common ones are supported: `%yyyy`, `%yy`,
 * `%mm`, `%m`, `%dd`, `%d`, `%hh`, `%h`, `%ii`, `%i`, `%ss`, `%s`, `%fff` and
 * `%%`. When `format` is empty (the default), an **empty string** is returned.
 *
 * @param mixed $value The original value (ignored — replaced wholesale).
 * @param string $begin Earliest instant (ISO 8601). Defaults to the epoch.
 * @param string $end Latest instant (ISO 8601). Empty means "now".
 * @param string $format The DATE_FORMAT-style pattern. Empty returns "".
 * @return string
 *
 * @throws RandomException
 *
 * @example
 * ```php
 * use function oihana\masking\maskDatetime;
 *
 * maskDatetime( '2001-09-11' , '2019-01-01' , '2019-12-31' , '%yyyy-%mm-%dd' );
 * // e.g. "2019-06-17"
 *
 * maskDatetime( '2001-09-11' ); // "" (no format)
 * ```
 *
 * @package oihana\masking
 * @since 1.0.0
 * @author Marc Alcaraz
 */
function maskDatetime( mixed $value = null , string $begin = '1970-01-01T00:00:00.000' , string $end = '' , string $format = '' ) :string
{
    if( $format === '' )
    {
        return '' ;
    }

    $from = strtotime( $begin ) ;
    $from = $from === false ? 0 : $from ;

    $to = $end === '' ? time() : strtotime( $end ) ;
    $to = $to === false ? time() : $to ;

    if( $from > $to )
    {
        [ $from , $to ] = [ $to , $from ] ;
    }

    $ts = random_int( $from , $to ) ;

    return strtr( $format ,
    [
        '%yyyy' => gmdate( 'Y' , $ts ) ,
        '%yy'   => gmdate( 'y' , $ts ) ,
        '%mm'   => gmdate( 'm' , $ts ) ,
        '%m'    => gmdate( 'n' , $ts ) ,
        '%dd'   => gmdate( 'd' , $ts ) ,
        '%d'    => gmdate( 'j' , $ts ) ,
        '%hh'   => gmdate( 'H' , $ts ) ,
        '%h'    => gmdate( 'G' , $ts ) ,
        '%ii'   => gmdate( 'i' , $ts ) ,
        '%i'    => (string) (int) gmdate( 'i' , $ts ) ,
        '%ss'   => gmdate( 's' , $ts ) ,
        '%s'    => (string) (int) gmdate( 's' , $ts ) ,
        '%fff'  => '000' ,
        '%%'    => '%' ,
    ] ) ;
}
