<?php

namespace oihana\masking;

use InvalidArgumentException;
use Random\RandomException;

/**
 * Applies the masking rules to every element of a JSON array (a PHP list).
 *
 * Each element is handled on its own: a nested **list** recurses through this
 * helper, a nested **object** is walked by {@see maskDocumentNode()}, and a bare
 * **scalar** is left untouched (it is not a keyed attribute, so no path rule can
 * target it directly — only the masker applied to the parent leaf reaches it).
 * This mirrors the common "array elements are masked individually" rule.
 *
 * @param array $list     The list to walk.
 * @param array $maskings The list of rules for this collection.
 * @param int   $depth    The current depth (protected attributes are top-level only).
 * @param array<int,string> $protectedAttributes Top-level attribute names never masked. Default: none.
 * @return array The masked list.
 *
 * @throws InvalidArgumentException When a rule has no `type`, or an unknown masker.
 * @throws RandomException
 *
 * @example
 * ```php
 * use function oihana\masking\maskDocumentList;
 *
 * $rules = [ [ 'path' => '.name' , 'type' => 'xifyFront' , 'unmaskedLength' => 2 ] ] ;
 * maskDocumentList( [ [ 'name' => 'hugo' ] , 'egon' ] , $rules , 1 ) ;
 * // [ [ 'name' => 'xxgo' ] , 'egon' ]
 * ```
 *
 * @package oihana\masking
 * @since 1.0.0
 * @author Marc Alcaraz
 */
function maskDocumentList( array $list , array $maskings , int $depth , array $protectedAttributes = [] ) :array
{
    return array_map
    (
        static function( mixed $element ) use ( $maskings , $depth , $protectedAttributes ): mixed
        {
            if ( !is_array( $element ) )
            {
                return $element ;
            }

            return array_is_list( $element )
                 ? maskDocumentList( $element , $maskings , $depth , $protectedAttributes )
                 : maskDocumentNode( $element , $maskings , null , $depth , $protectedAttributes ) ;
        } ,
        $list ,
    ) ;
}
