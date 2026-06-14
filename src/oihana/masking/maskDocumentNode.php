<?php

namespace oihana\masking;

use InvalidArgumentException;
use Random\RandomException;

/**
 * Walks an object: masks the matching leaves and descends into nested objects
 * and arrays.
 *
 * A leaf (scalar, `null` or a JSON array) is masked by the first matching rule
 * ({@see resolveMaskingRule()}); an array with no matching rule is walked deeper
 * ({@see maskDocumentList()}); a nested object recurses through this helper. The
 * top-level system attributes ({@see maskingSystemAttributes()}) are never masked.
 *
 * @param array       $node      The object to walk.
 * @param array       $maskings  The list of rules for this collection.
 * @param string|null $exactPath The dotted path of `$node` (null once an array has been crossed).
 * @param int         $depth     The current depth (system attributes are top-level only).
 * @return array The masked object.
 *
 * @throws InvalidArgumentException When a rule has no `type`, or an unknown masker.
 * @throws RandomException
 *
 * @example
 * ```php
 * use function oihana\masking\maskDocumentNode;
 *
 * $rules = [ [ 'path' => 'email' , 'type' => 'email' ] ] ;
 * maskDocumentNode( [ '_key' => 'a' , 'email' => 'real@example.com' ] , $rules , '' , 0 ) ;
 * // [ '_key' => 'a' , 'email' => 'aZ12.bY34@cX56.invalid' ]
 * ```
 *
 * @package oihana\masking
 * @since 1.0.0
 * @author Marc Alcaraz
 */
function maskDocumentNode( array $node , array $maskings , ?string $exactPath , int $depth ) :array
{
    $system = maskingSystemAttributes() ;
    $out    = [] ;

    foreach( $node as $key => $value )
    {
        $key = (string) $key ;

        if( $depth === 0 && in_array( $key , $system , true ) )
        {
            $out[ $key ] = $value ;
            continue ;
        }

        $childPath = $exactPath === null ? null : ( $exactPath === '' ? $key : $exactPath . '.' . $key ) ;

        if( is_array( $value ) && !array_is_list( $value ) )
        {
            $out[ $key ] = maskDocumentNode( $value , $maskings , $childPath , $depth + 1 ) ;
            continue ;
        }

        // Leaf (scalar, null or list array).
        $rule = resolveMaskingRule( $maskings , $key , $childPath ) ;
        if( $rule !== null )
        {
            if( !isset( $rule[ 'type' ] ) || !is_string( $rule[ 'type' ] ) )
            {
                throw new InvalidArgumentException( sprintf( "Masking rule for path '%s' has no type." , $rule[ 'path' ] ?? '?' ) ) ;
            }
            $out[ $key ] = maskValue( $rule[ 'type' ] , $value , $rule ) ;
        }
        elseif( is_array( $value ) )
        {
            $out[ $key ] = maskDocumentList( $value , $maskings , $depth + 1 ) ; // no rule on the array: look deeper
        }
        else
        {
            $out[ $key ] = $value ;
        }
    }

    return $out ;
}
