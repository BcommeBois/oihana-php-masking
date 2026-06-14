<?php

namespace oihana\masking\enums;

use oihana\reflect\traits\ConstantsTrait;

/**
 * The masker-specific option keys carried by a masking rule.
 *
 * These are the extra keys read by {@see oihana\masking\maskValue()} beyond the
 * structural {@see MaskingRule} keys (`path`, `type`). Each masker reads only the
 * options that apply to it (see the maskers catalogue).
 *
 * @package oihana\masking\enums
 * @since 1.0.0
 * @author Marc Alcaraz
 */
class MaskingOption
{
    use ConstantsTrait ;

    /**
     * `datetime` — earliest instant (ISO 8601).
     */
    public const string BEGIN = 'begin' ;

    /**
     * `phone` / `zip` — fallback value when the input is not a string.
     */
    public const string DEFAULT = 'default' ;

    /**
     * `datetime` — latest instant (ISO 8601); empty means "now".
     */
    public const string END = 'end' ;

    /**
     * `datetime` — the DATE_FORMAT-style output pattern.
     */
    public const string FORMAT = 'format' ;

    /**
     * `xifyFront` — append a short hash to reduce collisions.
     */
    public const string HASH = 'hash' ;

    /**
     * `decimal` / `integer` — smallest value to return.
     */
    public const string LOWER = 'lower' ;

    /**
     * `decimal` — maximum number of fraction digits.
     */
    public const string SCALE = 'scale' ;

    /**
     * `xifyFront` — secret used by the hash (0 = unseeded).
     */
    public const string SEED = 'seed' ;

    /**
     * `xifyFront` — how many trailing characters of each word to keep.
     */
    public const string UNMASKED_LENGTH = 'unmaskedLength' ;

    /**
     * `decimal` / `integer` — largest value to return.
     */
    public const string UPPER = 'upper' ;
}
