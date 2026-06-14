<?php

namespace oihana\masking;

use Random\RandomException;

/**
 * Replaces a zip / postal code with a random one of the same shape.
 *
 * Implements the conventional `zip` masker: each **digit** becomes a random digit,
 * each **letter** a random letter (case kept), other characters are left
 * unchanged. When the value is not a string, the `default` fallback is used.
 *
 * @param mixed $value The original value.
 * @param string $default Fallback when the value is not a string.
 * @return string
 *
 * @throws RandomException
 *
 * @example
 * ```php
 * use function oihana\masking\maskZip;
 *
 * maskZip( '50674' );   // e.g. "98146"
 * maskZip( 'SA34-EA' ); // e.g. "OW91-JI"
 * maskZip( null );      // "12345" (default)
 * ```
 *
 * @package oihana\masking
 * @since 1.0.0
 * @author Marc Alcaraz
 */
function maskZip( mixed $value , string $default = '12345' ) :string
{
    if( !is_string( $value ) )
    {
        return $default ;
    }

    $out = '' ;
    foreach( str_split( $value ) as $char )
    {
        if( ctype_digit( $char ) )
        {
            $out .= random_int( 0 , 9 ) ;
        }
        elseif( ctype_alpha( $char ) )
        {
            $out .= chr( random_int( 0 , 25 ) + ( ctype_upper( $char ) ? 65 : 97 ) ) ;
        }
        else
        {
            $out .= $char ;
        }
    }

    return $out ;
}
