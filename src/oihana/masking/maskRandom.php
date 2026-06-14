<?php

namespace oihana\masking;

use Random\RandomException;

/**
 * Replaces a leaf value with a random value of the **same kind**.
 *
 * Implements the conventional `random` masker:
 *  - strings   → a random string (see {@see maskRandomString()}) ;
 *  - integers  → a random integer between -1000 and 1000 ;
 *  - floats    → a random decimal between -1000 and 1000 ;
 *  - booleans  → a random boolean ;
 *  - `null`    → stays `null`.
 *
 * @param mixed $value The original value.
 * @return mixed The randomized value, type-preserved.
 *
 * @throws RandomException
 *
 * @example
 * ```php
 * use function oihana\masking\maskRandom;
 *
 * maskRandom( 'hello' ); // e.g. "x7Bqz"
 * maskRandom( 42 );      // e.g. -738
 * maskRandom( 2.34 );    // e.g. 91.7
 * maskRandom( true );    // e.g. false
 * maskRandom( null );    // null
 * ```
 *
 * @package oihana\masking
 * @since 1.0.0
 * @author Marc Alcaraz
 */
function maskRandom( mixed $value ) :mixed
{
    if( $value === null )
    {
        return null ;
    }

    if( is_bool( $value ) )
    {
        return random_int( 0 , 1 ) === 1 ;
    }

    if( is_int( $value ) )
    {
        return random_int( -1000 , 1000 ) ;
    }

    if( is_float( $value ) )
    {
        return random_int( -1000 * 100 , 1000 * 100 ) / 100 ;
    }

    if( is_string( $value ) )
    {
        return randomAlphaNumeric( max( 1 , strlen( $value ) ) ) ;
    }

    return $value ;
}
