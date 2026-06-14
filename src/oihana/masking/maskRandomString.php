<?php

namespace oihana\masking;

use Random\RandomException;

/**
 * Replaces a string value with a random anonymized string.
 *
 * Implements the conventional `randomString` masker: **only string values are
 * modified** — any other type (number, boolean, `null`) is returned unchanged.
 * The replacement keeps the original length (with a minimum of 1).
 *
 * The replacement is random, not a reversible hash of the input — the goal is
 * anonymization, not byte-compatibility with any external tool.
 *
 * @param mixed $value The original value.
 * @return mixed The masked string, or the value unchanged when not a string.
 *
 * @throws RandomException
 *
 * @example
 * ```php
 * use function oihana\masking\maskRandomString;
 *
 * maskRandomString( 'My Name' ); // e.g. "x7Bqz9a"
 * maskRandomString( 1234 );      // 1234 (non-string, unchanged)
 * ```
 *
 * @package oihana\masking
 * @since 1.0.0
 * @author Marc Alcaraz
 */
function maskRandomString( mixed $value ) :mixed
{
    if( !is_string( $value ) )
    {
        return $value ;
    }

    return randomAlphaNumeric( max( 1 , strlen( $value ) ) ) ;
}
