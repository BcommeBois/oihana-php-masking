<?php

namespace oihana\masking\enums;

use oihana\reflect\traits\ConstantsTrait;

/**
 * The attribute masking functions (the `type` of a masking rule).
 *
 * The names follow a conventional data-masking vocabulary, so the **same** rule
 * set can drive this portable PHP engine and any compatible external tool that
 * shares the same masker names.
 *
 * @package oihana\masking\enums
 * @since 1.0.0
 * @author Marc Alcaraz
 */
class Masker
{
    use ConstantsTrait ;

    public const string CREDIT_CARD   = 'creditCard' ;
    public const string DATETIME      = 'datetime' ;
    public const string DECIMAL       = 'decimal' ;
    public const string EMAIL         = 'email' ;
    public const string INTEGER       = 'integer' ;
    public const string PHONE         = 'phone' ;
    public const string RANDOM        = 'random' ;
    public const string RANDOM_STRING = 'randomString' ;
    public const string XIFY_FRONT    = 'xifyFront' ;
    public const string ZIP           = 'zip' ;
}
