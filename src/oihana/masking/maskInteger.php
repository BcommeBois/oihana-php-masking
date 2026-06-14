<?php

namespace oihana\masking;

use Random\RandomException;

/**
 * Replaces a value with a random integer in `[lower, upper]`.
 *
 * Implements the conventional `integer` masker: the value is replaced **whatever
 * its original type** (string, boolean and `null` included). The bounds are
 * inclusive; they are swapped if given in the wrong order.
 *
 * @param mixed $value The original value (ignored — replaced wholesale).
 * @param int $lower Smallest value to return.
 * @param int $upper Largest value to return.
 *
 * @return int
 *
 * @throws RandomException
 *
 * @example
 * ```php
 * use function oihana\masking\maskInteger;
 *
 * maskInteger( 9999 );          // e.g. 42   (default range -100..100)
 * maskInteger( 'x' , 0 , 10 );  // e.g. 7
 * ```
 *
 * @package oihana\masking
 * @since 1.0.0
 * @author Marc Alcaraz
 */
function maskInteger( mixed $value = null , int $lower = -100 , int $upper = 100 ) :int
{
    if( $lower > $upper )
    {
        [ $lower , $upper ] = [ $upper , $lower ] ;
    }

    return random_int( $lower , $upper ) ;
}
