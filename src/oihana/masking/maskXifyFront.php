<?php

namespace oihana\masking;

/**
 * Masks the front of each word with `x`, keeping a few trailing characters.
 *
 * Implements the conventional `xifyFront` masker. Within each **word** (a run of
 * alphanumeric, `_` or `-` characters) every character except the last
 * `unmaskedLength` ones is replaced by `x`; words no longer than
 * `unmaskedLength` are left untouched. Every other character (spaces,
 * punctuation) becomes a blank. Non-string values (boolean, number) become the
 * fixed string `"xxxx"`, and `null` stays `null`.
 *
 * @example
 * ```php
 * use function oihana\masking\maskXifyFront;
 *
 * maskXifyFront( 'This is a test!Do you agree?' ); // "xxis is a xxst Do xou xxxee "
 * maskXifyFront( true );                           // "xxxx"
 * maskXifyFront( null );                           // null
 * maskXifyFront( 'secret' , 4 );                   // "xxcret"
 * ```
 *
 * @param mixed $value          The original value.
 * @param int   $unmaskedLength How many trailing characters of each word to keep.
 * @param bool  $hash           Append a short hash to reduce collisions.
 * @param int   $seed           Secret used by the hash (0 = unseeded).
 * @return mixed The masked string, `"xxxx"` for non-strings, or `null`.
 *
 * @package oihana\masking
 * @since 1.0.0
 * @author Marc Alcaraz
 */
function maskXifyFront( mixed $value , int $unmaskedLength = 2 , bool $hash = false , int $seed = 0 ) :mixed
{
    if( $value === null )
    {
        return null ;
    }

    if( !is_string( $value ) )
    {
        return 'xxxx' ;
    }

    $maskWord = static function( string $word ) use ( $unmaskedLength ) :string
    {
        $length = strlen( $word ) ;
        if( $length <= $unmaskedLength )
        {
            return $word ;
        }
        return str_repeat( 'x' , $length - $unmaskedLength ) . substr( $word , $length - $unmaskedLength ) ;
    } ;

    $out  = '' ;
    $word = '' ;
    foreach( str_split( $value ) as $char )
    {
        if( ctype_alnum( $char ) || $char === '_' || $char === '-' )
        {
            $word .= $char ;
        }
        else
        {
            $out .= $maskWord( $word ) . ' ' ;
            $word = '' ;
        }
    }
    $out .= $maskWord( $word ) ;

    if( $hash )
    {
        $out .= substr( md5( $value . $seed ) , 0 , 8 ) ;
    }

    return $out ;
}
