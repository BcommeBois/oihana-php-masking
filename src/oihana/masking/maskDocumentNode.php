<?php

namespace oihana\masking;

use InvalidArgumentException;
use Random\RandomException;

use oihana\masking\enums\MaskingRule;

/**
 * Walks an object: masks the matching leaves and descends into nested objects
 * and arrays.
 *
 * A leaf (scalar, `null` or a JSON array) is masked by the first matching rule
 * ({@see resolveMaskingRule()}); an array with no matching rule is walked deeper
 * ({@see maskDocumentList()}); a nested object recurses through this helper. The
 * attributes named in `$protectedAttributes` are never masked at the **top
 * level** (`$depth === 0`); the default is an empty list (nothing protected).
 *
 * @param array       $node      The object to walk.
 * @param array       $maskings  The list of rules for this collection.
 * @param string|null $exactPath The dotted path of `$node` (null once an array has been crossed).
 * @param int         $depth     The current depth (protected attributes are top-level only).
 * @param array<int,string> $protectedAttributes Top-level attribute names never masked. Default: none.
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
 * maskDocumentNode( [ '_key' => 'a' , 'email' => 'real@example.com' ] , $rules , '' , 0 , [ '_key' ] ) ;
 * // [ '_key' => 'a' , 'email' => 'aZ12.bY34@cX56.invalid' ]
 * ```
 *
 * @package oihana\masking
 * @since 1.0.0
 * @author Marc Alcaraz
 */
function maskDocumentNode( array $node , array $maskings , ?string $exactPath , int $depth , array $protectedAttributes = [] ) :array
{
    $out = [] ;

    foreach( $node as $key => $value )
    {
        $key = (string) $key ;

        if( $depth === 0 && in_array( $key , $protectedAttributes , true ) )
        {
            $out[ $key ] = $value ;
            continue ;
        }

        $childPath = $exactPath === null ? null : ( $exactPath === '' ? $key : $exactPath . '.' . $key ) ;

        if( is_array( $value ) && !array_is_list( $value ) )
        {
            $out[ $key ] = maskDocumentNode( $value , $maskings , $childPath , $depth + 1 , $protectedAttributes ) ;
            continue ;
        }

        // Leaf (scalar, null or list array).
        $rule = resolveMaskingRule( $maskings , $key , $childPath ) ;
        if( $rule !== null )
        {
            if( !isset( $rule[ MaskingRule::TYPE ] ) || !is_string( $rule[ MaskingRule::TYPE ] ) )
            {
                throw new InvalidArgumentException( sprintf( "Masking rule for path '%s' has no type." , $rule[ MaskingRule::PATH ] ?? '?' ) ) ;
            }
            $out[ $key ] = maskValue( $rule[ MaskingRule::TYPE ] , $value , $rule ) ;
        }
        elseif( is_array( $value ) )
        {
            $out[ $key ] = maskDocumentList( $value , $maskings , $depth + 1 , $protectedAttributes ) ; // no rule on the array: look deeper
        }
        else
        {
            $out[ $key ] = $value ;
        }
    }

    return $out ;
}
