<?php

namespace oihana\masking;

use Random\RandomException;

/**
 * Returns a random alphanumeric string of the given length.
 *
 * A small building block shared by the string-producing maskers
 * ({@see maskEmail()}, {@see maskRandomString()}, {@see maskRandom()}). It is
 * **not** meant to be cryptographically reversible — its only purpose is to
 * replace a value with anonymized, readable characters.
 *
 * @param int $length The desired length (clamped to a minimum of 1).
 * @return string
 *
 * @throws RandomException
 *
 * @example
 * ```php
 * use function oihana\masking\randomAlphaNumeric;
 *
 * randomAlphaNumeric( 4 ); // e.g. "x7Bq"
 * ```
 *
 * @package oihana\masking
 * @since 1.0.0
 * @author Marc Alcaraz
 */
function randomAlphaNumeric( int $length ) :string
{
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789' ;
    $max      = strlen( $alphabet ) - 1 ;
    $length   = max( 1 , $length ) ;

    $out = '' ;
    for( $i = 0 ; $i < $length ; $i++ )
    {
        $out .= $alphabet[ random_int( 0 , $max ) ] ;
    }

    return $out ;
}
