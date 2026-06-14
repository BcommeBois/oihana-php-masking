<?php

namespace oihana\masking;

/**
 * The top-level document-store system attributes that are never masked.
 *
 * By default these are the identity / edge-reference fields `_key`, `_id`,
 * `_rev`, `_from` and `_to`: they identify a document (or the endpoints of an
 * edge) and must survive masking untouched. The names follow the document-store
 * convention (e.g. ArangoDB) but the list is plain data — reuse or override it
 * to fit any backend.
 *
 * @return array<int,string>
 *
 * @example
 * ```php
 * use function oihana\masking\maskingSystemAttributes;
 *
 * in_array( '_key' , maskingSystemAttributes() , true ); // true
 * ```
 *
 * @package oihana\masking
 * @since 1.0.0
 * @author Marc Alcaraz
 */
function maskingSystemAttributes() :array
{
    return [ '_key' , '_id' , '_rev' , '_from' , '_to' ] ;
}
