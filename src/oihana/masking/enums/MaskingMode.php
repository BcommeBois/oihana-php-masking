<?php

namespace oihana\masking\enums;

use oihana\reflect\traits\ConstantsTrait;

/**
 * The document-level masking modes (the per-collection `type`).
 *
 *  - `exclude`   : the collection is ignored entirely ;
 *  - `structure` : only the collection metadata is kept, no document data ;
 *  - `masked`    : structure and data, with the attribute rules applied ;
 *  - `full`      : structure and data kept verbatim, no masking.
 *
 * @package oihana\masking\enums
 * @since 1.0.0
 * @author Marc Alcaraz
 */
class MaskingMode
{
    use ConstantsTrait ;

    public const string EXCLUDE   = 'exclude' ;
    public const string FULL      = 'full' ;
    public const string MASKED    = 'masked' ;
    public const string STRUCTURE = 'structure' ;
}
