<?php

namespace oihana\masking;

use Random\RandomException;

/**
 * Replaces a phone number with a random one of the same shape.
 *
 * Implements the conventional `phone` masker: each **digit** is replaced by a
 * random digit, each **letter** by a random letter (case kept), and every other
 * character is left unchanged. When the value is not a string, the `default`
 * fallback is used.
 *
 * @param mixed $value The original value.
 * @param string $default Fallback when the value is not a string.
 * @return string
 *
 * @throws RandomException
 *
 * @example
 * ```php
 * use function oihana\masking\maskPhone;
 *
 * maskPhone( '+31 66-77-88' ); // e.g. "+75 10-79-52"
 * maskPhone( 1234 );           // "+1234567890" (default, non-string input)
 * ```
 *
 * @package oihana\masking
 * @since 1.0.0
 * @author Marc Alcaraz
 */
function maskPhone( mixed $value , string $default = '+1234567890' ) :string
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
            $letter = chr( random_int( 0 , 25 ) + ( ctype_upper( $char ) ? 65 : 97 ) ) ;
            $out   .= $letter ;
        }
        else
        {
            $out .= $char ;
        }
    }

    return $out ;
}
