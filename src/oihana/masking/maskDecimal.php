<?php

namespace oihana\masking;

use Random\RandomException;

/**
 * Replaces a value with a random decimal in `[lower, upper]`.
 *
 * Implements the conventional `decimal` masker: the value is replaced **whatever
 * its original type** (string, boolean and `null` included), rounded to `scale`
 * fraction digits. The bounds are inclusive; they are swapped if given in the
 * wrong order.
 *
 * @param mixed $value The original value (ignored — replaced wholesale).
 * @param float $lower Smallest value to return.
 * @param float $upper Largest value to return.
 * @param int $scale Maximum number of fraction digits.
 * @return float
 *
 * @throws RandomException
 *
 * @example
 * ```php
 * use function oihana\masking\maskDecimal;
 *
 * maskDecimal( 3.14 );                       // e.g. -0.42 (default -1..1, scale 2)
 * maskDecimal( 'x' , -0.3 , 0.3 , 2 );       // e.g. 0.17
 * ```
 *
 * @package oihana\masking
 * @since 1.0.0
 * @author Marc Alcaraz
 */
function maskDecimal( mixed $value = null , float $lower = -1.0 , float $upper = 1.0 , int $scale = 2 ) :float
{
    if( $lower > $upper )
    {
        [ $lower , $upper ] = [ $upper , $lower ] ;
    }

    $scale  = max( 0 , $scale ) ;
    $factor = 10 ** $scale ;

    $low  = (int) round( $lower * $factor ) ;
    $high = (int) round( $upper * $factor ) ;

    return round( random_int( $low , $high ) / $factor , $scale ) ;
}
