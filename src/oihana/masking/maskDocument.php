<?php

namespace oihana\masking;

use InvalidArgumentException;
use Random\RandomException;

/**
 * Applies a list of attribute masking rules to a single document.
 *
 * This is a portable, self-contained masking engine. Each rule is
 * `{ "path": …, "type": <masker>, …params }`; the supported path forms are:
 *
 *  - `"name"`        — a leaf attribute `name` at the top level ;
 *  - `"a.b"`         — the exact nested path `a` → `b` (through objects only) ;
 *  - `".name"`       — every leaf attribute named `name`, at any depth ;
 *  - `"*"`           — every leaf attribute ;
 *  - `` "`a.b`" ``   — a literal attribute name containing dots (backtick/tick quoted).
 *
 * A **leaf** is a value that is `null`, a scalar or a JSON array; objects are
 * descended into. When a matched leaf is an array, the masker is applied to its
 * elements individually (see {@see maskValue()}). The top-level **system
 * attributes** `_key`, `_id`, `_rev`, `_from`, `_to` are never masked. When
 * several rules match the same leaf, the **first one** in the list wins.
 *
 * @param array $doc      The document (decoded JSON object).
 * @param array $maskings The list of rules for this collection.
 * @return array The masked document.
 *
 * @throws InvalidArgumentException When a rule has no `type`, or an unknown masker.
 * @throws RandomException
 *
 * @example
 * ```php
 * use function oihana\masking\maskDocument;
 *
 * $doc = [ '_key' => 'a' , 'email' => 'real@example.com' , 'profile' => [ 'name' => 'Jane' ] ] ;
 * maskDocument( $doc , [ [ 'path' => 'email' , 'type' => 'email' ] , [ 'path' => '.name' , 'type' => 'xifyFront' ] ] ) ;
 * // [ '_key' => 'a' , 'email' => 'aZ12.bY34@cX56.invalid' , 'profile' => [ 'name' => 'xxne' ] ]
 * ```
 *
 * @package oihana\masking
 * @since 1.0.0
 * @author Marc Alcaraz
 */
function maskDocument( array $doc , array $maskings ) :array
{
    if( $maskings === [] )
    {
        return $doc ;
    }

    return maskDocumentNode( $doc , $maskings , '' , 0 ) ;
}
