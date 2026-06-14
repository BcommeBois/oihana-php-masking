<?php

namespace oihana\masking;

/**
 * Returns the first masking rule (in declaration order) that targets a leaf.
 *
 * A leaf is identified by its attribute name (`$key`) and its exact dotted path
 * (`$exactPath`, which is `null` once an array has been crossed, disabling
 * exact-path matching). The supported path forms are:
 *
 *  - `"*"`          — matches every leaf ;
 *  - `` "`a.b`" ``  — a backtick/tick quoted literal attribute name ;
 *  - `".name"`      — any leaf whose attribute name is `name`, at any depth ;
 *  - `"a.b"`        — the exact dotted path.
 *
 * The first matching rule wins, so a more specific rule listed earlier shadows a
 * broader one listed later.
 *
 * @param array       $maskings  The list of rules for this collection.
 * @param string      $key       The attribute name of the leaf.
 * @param string|null $exactPath The exact dotted path of the leaf, or null.
 * @return array<string,mixed>|null The matching rule, or null.
 *
 * @example
 * ```php
 * use function oihana\masking\resolveMaskingRule;
 *
 * $rules = [ [ 'path' => 'person.name' , 'type' => 'xifyFront' ] ] ;
 * resolveMaskingRule( $rules , 'name' , 'person.name' ); // the rule
 * resolveMaskingRule( $rules , 'name' , 'other.name' );  // null
 * ```
 *
 * @package oihana\masking
 * @since 1.0.0
 * @author Marc Alcaraz
 */
function resolveMaskingRule( array $maskings , string $key , ?string $exactPath ) :?array
{
    foreach( $maskings as $rule )
    {
        $path = (string) ( $rule[ 'path' ] ?? '' ) ;

        if( $path === '*' )
        {
            return $rule ;
        }

        if( str_starts_with( $path , '`' ) || str_starts_with( $path , '´' ) )
        {
            if( $exactPath === trim( $path , '`´' ) )
            {
                return $rule ;
            }
            continue ;
        }

        if( $path !== '' && $path[ 0 ] === '.' )
        {
            if( $key === substr( $path , 1 ) )
            {
                return $rule ;
            }
            continue ;
        }

        if( $exactPath !== null && $exactPath === $path )
        {
            return $rule ;
        }
    }

    return null ;
}
