<?php

namespace oihana\masking\enums;

use oihana\reflect\traits\ConstantsTrait;

/**
 * The structural keys of a masking rule.
 *
 * A rule is an associative array `{ path, type, …options }`; these are its two
 * mandatory keys. The masker-specific option keys live in {@see MaskingOption}.
 *
 * @package oihana\masking\enums
 * @since 1.0.0
 * @author Marc Alcaraz
 */
class MaskingRule
{
    use ConstantsTrait ;

    /**
     * The locator: which leaves the rule applies to (exact, `.name`, `*`, quoted literal).
     */
    public const string PATH = 'path' ;

    /**
     * The masker to apply (a {@see Masker} name).
     */
    public const string TYPE = 'type' ;
}
