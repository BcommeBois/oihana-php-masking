<?php

namespace oihana\masking;

use Random\RandomException;

/**
 * Replaces a value with a random, Luhn-valid credit-card number.
 *
 * Implements the conventional `creditCard` masker: it returns a random 16-digit
 * number (as an integer) whose check digit satisfies the
 * {@link https://en.wikipedia.org/wiki/Luhn_algorithm Luhn algorithm}. The
 * original value is never reflected in the output.
 *
 * @param mixed $value The original value (ignored — replaced wholesale).
 * @return int A Luhn-valid 16-digit number.
 *
 * @throws RandomException
 *
 * @example
 * ```php
 * use function oihana\masking\maskCreditCard;
 *
 * maskCreditCard( '4111-1111-1111-1111' ); // e.g. 4143300214110028
 * ```
 *
 * @package oihana\masking
 * @since 1.0.0
 * @author Marc Alcaraz
 */
function maskCreditCard( mixed $value = null ) :int
{
    // 15 random digits (the first never 0 so the number keeps 16 digits).
    $digits = [ random_int( 1 , 9 ) ] ;
    for( $i = 1 ; $i < 15 ; $i++ )
    {
        $digits[] = random_int( 0 , 9 ) ;
    }

    // Luhn check digit over the 15 partial digits.
    $sum = 0 ;
    foreach( array_reverse( $digits ) as $index => $digit )
    {
        // Doubled positions are the odd indexes counting from the right of the partial number.
        if( $index % 2 === 0 )
        {
            $digit *= 2 ;
            if( $digit > 9 )
            {
                $digit -= 9 ;
            }
        }
        $sum += $digit ;
    }
    $digits[] = ( 10 - ( $sum % 10 ) ) % 10 ;

    return (int) implode( '' , $digits ) ;
}
