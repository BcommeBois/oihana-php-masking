<?php

namespace oihana\masking;

use InvalidArgumentException;
use Random\RandomException;

use oihana\masking\enums\Masker;

/**
 * Applies a single masker to a value.
 *
 * This is the dispatcher in front of the per-type maskers ({@see maskEmail()},
 * {@see maskXifyFront()}, …). When the value is a **JSON array** (a PHP list),
 * the masker is applied to **each element individually** — sub-arrays recurse,
 * nested objects (associative arrays) are left untouched — mirroring the
 * common "array elements are masked individually" rule. Scalars and
 * `null` are masked directly.
 *
 * @param string $type   The masker name ({@see Masker}).
 * @param mixed  $value  The value to mask.
 * @param array  $params The masker parameters (e.g. `unmaskedLength`, `lower`).
 * @return mixed The masked value.
 *
 * @throws InvalidArgumentException When the masker name is unknown.
 * @throws RandomException
 *
 * @example
 * ```php
 * use function oihana\masking\maskValue;
 *
 * maskValue( 'email' , 'real@example.com' );             // "x7Bq.9aMz@Kp3R.invalid"
 * maskValue( 'xifyFront' , 'secret' , [ 'unmaskedLength' => 3 ] ); // "xxxret"
 * maskValue( 'random' , [ 1 , 'two' , true ] );          // [ -42, "x9Bz", false ]
 * ```
 *
 * @package oihana\masking
 * @since 1.0.0
 * @author Marc Alcaraz
 */
function maskValue( string $type , mixed $value , array $params = [] ) :mixed
{
    if( is_array( $value ) && array_is_list( $value ) )
    {
        return array_map
        (
            static fn( $element ) => ( is_array( $element ) && !array_is_list( $element ) )
                                   ? $element                              // nested object: untouched
                                   : maskValue( $type , $element , $params ) , // scalar or sub-list
            $value ,
        ) ;
    }

    return match( $type )
    {
        Masker::CREDIT_CARD   => maskCreditCard( $value ) ,
        Masker::DATETIME      => maskDatetime( $value , $params[ 'begin' ] ?? '1970-01-01T00:00:00.000' , $params[ 'end' ] ?? '' , $params[ 'format' ] ?? '' ) ,
        Masker::DECIMAL       => maskDecimal( $value , (float) ( $params[ 'lower' ] ?? -1 ) , (float) ( $params[ 'upper' ] ?? 1 ) , (int) ( $params[ 'scale' ] ?? 2 ) ) ,
        Masker::EMAIL         => maskEmail( $value ) ,
        Masker::INTEGER       => maskInteger( $value , (int) ( $params[ 'lower' ] ?? -100 ) , (int) ( $params[ 'upper' ] ?? 100 ) ) ,
        Masker::PHONE         => maskPhone( $value , (string) ( $params[ 'default' ] ?? '+1234567890' ) ) ,
        Masker::RANDOM        => maskRandom( $value ) ,
        Masker::RANDOM_STRING => maskRandomString( $value ) ,
        Masker::XIFY_FRONT    => maskXifyFront( $value , (int) ( $params[ 'unmaskedLength' ] ?? 2 ) , (bool) ( $params[ 'hash' ] ?? false ) , (int) ( $params[ 'seed' ] ?? 0 ) ) ,
        Masker::ZIP           => maskZip( $value , (string) ( $params[ 'default' ] ?? '12345' ) ) ,
        default               => throw new InvalidArgumentException( sprintf( "Unknown masker '%s'. Valid maskers: %s." , $type , implode( ', ' , Masker::getAll() ) ) ) ,
    } ;
}
