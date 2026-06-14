<?php

namespace tests\oihana\masking;

use InvalidArgumentException;

use PHPUnit\Framework\TestCase;

use function oihana\masking\maskCreditCard;
use function oihana\masking\maskDatetime;
use function oihana\masking\maskDecimal;
use function oihana\masking\maskDocument;
use function oihana\masking\maskDocumentList;
use function oihana\masking\maskDocumentNode;
use function oihana\masking\resolveMaskingRule;
use function oihana\masking\maskEmail;
use function oihana\masking\maskInteger;
use function oihana\masking\maskPhone;
use function oihana\masking\maskRandom;
use function oihana\masking\maskRandomString;
use function oihana\masking\maskValue;
use function oihana\masking\maskXifyFront;
use function oihana\masking\maskZip;
use function oihana\masking\randomAlphaNumeric;

/**
 * Unit coverage for the portable masking engine ({@see oihana\masking}).
 */
class MaskingsTest extends TestCase
{
    // ------------------------------------------------------------------ randomAlphaNumeric

    public function testRandomAlphaNumericLengthAndAlphabet() :void
    {
        $value = randomAlphaNumeric( 12 ) ;
        $this->assertSame( 12 , strlen( $value ) ) ;
        $this->assertMatchesRegularExpression( '/^[A-Za-z0-9]+$/' , $value ) ;
    }

    public function testRandomAlphaNumericClampsToOne() :void
    {
        $this->assertSame( 1 , strlen( randomAlphaNumeric( 0 ) ) ) ;
    }

    // ------------------------------------------------------------------ maskEmail

    public function testMaskEmailShapeAndAnonymization() :void
    {
        $masked = maskEmail( 'real.person@example.com' ) ;
        $this->assertMatchesRegularExpression( '/^[A-Za-z0-9]{4}\.[A-Za-z0-9]{4}@[A-Za-z0-9]{4}\.invalid$/' , $masked ) ;
        $this->assertStringNotContainsString( 'real.person' , $masked ) ;
    }

    // ------------------------------------------------------------------ maskPhone

    public function testMaskPhoneKeepsShapeRandomizesAlnum() :void
    {
        $masked = maskPhone( '+31 6A-77' ) ;
        $this->assertSame( 9 , strlen( $masked ) ) ;
        $this->assertSame( '+' , $masked[ 0 ] ) ;          // non-alnum kept
        $this->assertSame( ' ' , $masked[ 3 ] ) ;          // space kept
        $this->assertSame( '-' , $masked[ 6 ] ) ;          // dash kept
        $this->assertTrue( ctype_digit( $masked[ 1 ] ) ) ; // digit -> digit
        $this->assertTrue( ctype_upper( $masked[ 5 ] ) ) ; // 'A' -> upper letter
    }

    public function testMaskPhonePreservesLetterCaseBothWays() :void
    {
        // 'Ab' exercises both the upper and the lower branch of the case mapping.
        $masked = maskPhone( 'Ab' ) ;
        $this->assertSame( 2 , strlen( $masked ) ) ;
        $this->assertTrue( ctype_upper( $masked[ 0 ] ) ) ; // 'A' -> upper letter
        $this->assertTrue( ctype_lower( $masked[ 1 ] ) ) ; // 'b' -> lower letter
    }

    public function testMaskPhoneNonStringUsesDefault() :void
    {
        $this->assertSame( '+1234567890' , maskPhone( 1234 ) ) ;
        $this->assertSame( 'NONE' , maskPhone( null , 'NONE' ) ) ;
    }

    // ------------------------------------------------------------------ maskZip

    public function testMaskZipKeepsShape() :void
    {
        $masked = maskZip( 'SA34-EA' ) ;
        $this->assertSame( 7 , strlen( $masked ) ) ;
        $this->assertSame( '-' , $masked[ 4 ] ) ;
        $this->assertTrue( ctype_upper( $masked[ 0 ] ) ) ;
        $this->assertTrue( ctype_digit( $masked[ 2 ] ) ) ;
    }

    public function testMaskZipPreservesLetterCaseBothWays() :void
    {
        // 'Ab' exercises both the upper and the lower branch of the case mapping.
        $masked = maskZip( 'Ab' ) ;
        $this->assertSame( 2 , strlen( $masked ) ) ;
        $this->assertTrue( ctype_upper( $masked[ 0 ] ) ) ; // 'A' -> upper letter
        $this->assertTrue( ctype_lower( $masked[ 1 ] ) ) ; // 'b' -> lower letter
    }

    public function testMaskZipNonStringUsesDefault() :void
    {
        $this->assertSame( '12345' , maskZip( null ) ) ;
        $this->assertSame( 'abcdef' , maskZip( true , 'abcdef' ) ) ;
    }

    // ------------------------------------------------------------------ maskCreditCard

    public function testMaskCreditCardIsLuhnValid16Digits() :void
    {
        $number = (string) maskCreditCard( '4111-1111-1111-1111' ) ;
        $this->assertSame( 16 , strlen( $number ) ) ;

        $sum = 0 ;
        foreach( array_reverse( str_split( $number ) ) as $i => $d )
        {
            $d = (int) $d ;
            if( $i % 2 === 1 )
            {
                $d *= 2 ;
                if( $d > 9 ) { $d -= 9 ; }
            }
            $sum += $d ;
        }
        $this->assertSame( 0 , $sum % 10 , 'The generated number must satisfy the Luhn checksum.' ) ;
    }

    // ------------------------------------------------------------------ maskRandomString

    public function testMaskRandomStringReplacesStringsKeepsOthers() :void
    {
        $masked = maskRandomString( 'My Name' ) ;
        $this->assertSame( 7 , strlen( $masked ) ) ;
        $this->assertNotSame( 'My Name' , $masked ) ;

        $this->assertSame( 1234 , maskRandomString( 1234 ) ) ;
        $this->assertNull( maskRandomString( null ) ) ;
    }

    // ------------------------------------------------------------------ maskRandom

    public function testMaskRandomIsTypePreserving() :void
    {
        $this->assertNull( maskRandom( null ) ) ;
        $this->assertIsBool( maskRandom( true ) ) ;

        $int = maskRandom( 42 ) ;
        $this->assertIsInt( $int ) ;
        $this->assertGreaterThanOrEqual( -1000 , $int ) ;
        $this->assertLessThanOrEqual( 1000 , $int ) ;

        $this->assertIsFloat( maskRandom( 2.34 ) ) ;
        $this->assertIsString( maskRandom( 'hello' ) ) ;

        // Non-scalar, non-null falls through unchanged.
        $this->assertSame( [ 1 , 2 ] , maskRandom( [ 1 , 2 ] ) ) ;
    }

    // ------------------------------------------------------------------ maskXifyFront

    public function testMaskXifyFrontMatchesTheReferenceExample() :void
    {
        $this->assertSame( 'xxis is a xxst Do xou xxxee ' , maskXifyFront( 'This is a test!Do you agree?' ) ) ;
    }

    public function testMaskXifyFrontEdgeCases() :void
    {
        $this->assertNull( maskXifyFront( null ) ) ;
        $this->assertSame( 'xxxx' , maskXifyFront( true ) ) ;
        $this->assertSame( 'xxxx' , maskXifyFront( 12.3 ) ) ;
        $this->assertSame( 'ab' , maskXifyFront( 'ab' ) ) ;          // word length <= unmaskedLength
        $this->assertSame( 'xxxret' , maskXifyFront( 'secret' , 3 ) ) ;
    }

    public function testMaskXifyFrontHashAppends() :void
    {
        $masked = maskXifyFront( 'secret' , 2 , true , 7 ) ;
        $this->assertStringStartsWith( 'xxxxet' , $masked ) ;
        $this->assertSame( 14 , strlen( $masked ) ) ;                // 6 + 8-char hash
    }

    // ------------------------------------------------------------------ maskInteger / maskDecimal

    public function testMaskIntegerWithinBoundsAnyType() :void
    {
        $this->assertGreaterThanOrEqual( 0 , maskInteger( 'x' , 0 , 10 ) ) ;
        $this->assertLessThanOrEqual( 10 , maskInteger( null , 0 , 10 ) ) ;
        $this->assertSame( 5 , maskInteger( true , 5 , 5 ) ) ;
        // Swapped bounds are normalized.
        $this->assertSame( 5 , maskInteger( 0 , 5 , 5 ) ) ;
        $swapped = maskInteger( 0 , 10 , 0 ) ;
        $this->assertGreaterThanOrEqual( 0 , $swapped ) ;
        $this->assertLessThanOrEqual( 10 , $swapped ) ;
    }

    public function testMaskDecimalWithinBoundsAndScale() :void
    {
        $value = maskDecimal( 'x' , -0.3 , 0.3 , 2 ) ;
        $this->assertIsFloat( $value ) ;
        $this->assertGreaterThanOrEqual( -0.3 , $value ) ;
        $this->assertLessThanOrEqual( 0.3 , $value ) ;

        // Swapped bounds + negative scale clamp.
        $this->assertSame( 0.0 , maskDecimal( null , 0.0 , 0.0 , -2 ) ) ;
        $clamped = maskDecimal( 1 , 1.0 , -1.0 ) ;
        $this->assertGreaterThanOrEqual( -1.0 , $clamped ) ;
    }

    // ------------------------------------------------------------------ maskDatetime

    public function testMaskDatetimeEmptyFormatReturnsEmptyString() :void
    {
        $this->assertSame( '' , maskDatetime( 'whatever' ) ) ;
    }

    public function testMaskDatetimeFormatsWithinRange() :void
    {
        $out = maskDatetime( null , '2019-01-01' , '2019-12-31' , '%yyyy-%mm-%dd %hh:%ii:%ss %% %fff' ) ;
        $this->assertMatchesRegularExpression( '/^2019-\d{2}-\d{2} \d{2}:\d{2}:\d{2} % 000$/' , $out ) ;
    }

    public function testMaskDatetimeShortTokensAndSwappedBounds() :void
    {
        $out = maskDatetime( null , '2020-12-31' , '2020-01-01' , '%m/%d/%h/%i/%s' ) ; // swapped begin/end
        $this->assertMatchesRegularExpression( '#^\d{1,2}/\d{1,2}/\d{1,2}/\d{1,2}/\d{1,2}$#' , $out ) ;
    }

    public function testMaskDatetimeFallsBackOnInvalidBounds() :void
    {
        // Unparseable begin (-> epoch) and end (-> now); just expect a 4-digit year.
        $out = maskDatetime( null , 'not-a-date' , 'also-bad' , '%yyyy' ) ;
        $this->assertMatchesRegularExpression( '/^\d{4}$/' , $out ) ;
    }

    public function testMaskDatetimeEmptyEndDefaultsToNow() :void
    {
        // Empty end with a non-empty format -> the upper bound is "now": the
        // random year lands between the begin year and the current year.
        $out = maskDatetime( null , '2000-01-01' , '' , '%yyyy' ) ;
        $this->assertMatchesRegularExpression( '/^\d{4}$/' , $out ) ;
        $this->assertGreaterThanOrEqual( 2000 , (int) $out ) ;
        $this->assertLessThanOrEqual( (int) gmdate( 'Y' ) , (int) $out ) ;
    }

    // ------------------------------------------------------------------ maskValue

    public function testMaskValueDispatchesEveryType() :void
    {
        $this->assertMatchesRegularExpression( '/\.invalid$/' , maskValue( 'email' , 'a@b.c' ) ) ;
        $this->assertIsInt( maskValue( 'creditCard' , null ) ) ;
        $this->assertSame( '+1234567890' , maskValue( 'phone' , 1 ) ) ;
        $this->assertSame( '12345' , maskValue( 'zip' , 1 ) ) ;
        $this->assertSame( 7 , maskValue( 'integer' , 'x' , [ 'lower' => 7 , 'upper' => 7 ] ) ) ;
        $this->assertSame( 0.0 , maskValue( 'decimal' , 'x' , [ 'lower' => 0 , 'upper' => 0 ] ) ) ;
        $this->assertSame( 'AB', strtoupper( substr( maskValue( 'xifyFront' , 'AB' ) , 0 , 2 ) ) ) ;
        $this->assertNull( maskValue( 'random' , null ) ) ;
        $this->assertSame( 9 , maskValue( 'integer' , true , [ 'lower' => 9 , 'upper' => 9 ] ) ) ;
        $this->assertIsString( maskValue( 'randomString' , 'abc' ) ) ;
        $this->assertMatchesRegularExpression( '/^\d{2}$/' , maskValue( 'datetime' , null , [ 'begin' => '2020', 'end' => '2020', 'format' => '%yy' ] ) ) ;
    }

    public function testMaskValueAppliesPerArrayElement() :void
    {
        $out = maskValue( 'integer' , [ 1 , [ 2 , 3 ] , [ 'o' => 'keep' ] , 'str' ] , [ 'lower' => 0 , 'upper' => 0 ] ) ;
        $this->assertSame( 0 , $out[ 0 ] ) ;             // scalar masked
        $this->assertSame( [ 0 , 0 ] , $out[ 1 ] ) ;     // sub-list recursed
        $this->assertSame( [ 'o' => 'keep' ] , $out[ 2 ] ) ; // nested object untouched
        $this->assertSame( 0 , $out[ 3 ] ) ;
    }

    public function testMaskValueUnknownTypeThrows() :void
    {
        $this->expectException( InvalidArgumentException::class ) ;
        $this->expectExceptionMessage( 'Unknown masker' ) ;
        maskValue( 'nope' , 'x' ) ;
    }

    // ------------------------------------------------------------------ maskDocument

    public function testMaskDocumentEmptyRulesReturnsDocument() :void
    {
        $doc = [ '_key' => 'a' , 'x' => 1 ] ;
        $this->assertSame( $doc , maskDocument( $doc , [] ) ) ;
    }

    public function testMaskDocumentSuffixDescendsObjectsAndArrays() :void
    {
        $doc = [
            '_key'      => 'a' ,
            'name'      => 'top-name' ,
            'nicknames' => [ [ 'name' => 'hugo' ] , 'egon' ] ,
            'other'     => [ 'name' => 'emil' , 'secret' => 'superman' ] ,
        ] ;

        $out = maskDocument( $doc , [ [ 'path' => '.name' , 'type' => 'xifyFront' , 'unmaskedLength' => 2 ] ] ) ;

        $this->assertSame( 'a' , $out[ '_key' ] ) ;                 // no rule matches _key (the `.name` rule misses it)
        $this->assertSame( 'xxxxxxme' , $out[ 'name' ] ) ;
        $this->assertSame( 'xxgo' , $out[ 'nicknames' ][ 0 ][ 'name' ] ) ;
        $this->assertSame( 'egon' , $out[ 'nicknames' ][ 1 ] ) ;   // bare array scalar untouched
        $this->assertSame( 'xxil' , $out[ 'other' ][ 'name' ] ) ;
        $this->assertSame( 'superman' , $out[ 'other' ][ 'secret' ] ) ;
    }

    public function testMaskDocumentExactPathOnlyMatchesThatPath() :void
    {
        $doc = [ 'person' => [ 'name' => 'foobar' ] , 'other' => [ 'name' => 'kepterm' ] ] ;
        $out = maskDocument( $doc , [ [ 'path' => 'person.name' , 'type' => 'xifyFront' , 'unmaskedLength' => 2 ] ] ) ;

        $this->assertSame( 'xxxxar' , $out[ 'person' ][ 'name' ] ) ;
        $this->assertSame( 'kepterm' , $out[ 'other' ][ 'name' ] ) ; // exact path does not match nested elsewhere
    }

    public function testMaskDocumentWildcardMasksEveryLeafButProtected() :void
    {
        $doc  = [ '_key' => 'k' , '_rev' => 'r' , 'n' => 5 , 'arr' => [ 1 , 2 ] , 'o' => [ 'x' => 9 ] ] ;
        $rule = [ [ 'path' => '*' , 'type' => 'integer' , 'lower' => 0 , 'upper' => 0 ] ] ;

        // The caller-provided protected list survives the wildcard.
        $out = maskDocument( $doc , $rule , [ '_key' , '_rev' ] ) ;
        $this->assertSame( 'k' , $out[ '_key' ] ) ;
        $this->assertSame( 'r' , $out[ '_rev' ] ) ;
        $this->assertSame( 0 , $out[ 'n' ] ) ;
        $this->assertSame( [ 0 , 0 ] , $out[ 'arr' ] ) ;
        $this->assertSame( 0 , $out[ 'o' ][ 'x' ] ) ;
    }

    public function testMaskDocumentProtectsNothingByDefault() :void
    {
        // No protected list -> the wildcard masks every top-level leaf, system fields included.
        $doc = [ '_key' => 'k' , '_rev' => 'r' , 'n' => 5 ] ;
        $out = maskDocument( $doc , [ [ 'path' => '*' , 'type' => 'integer' , 'lower' => 0 , 'upper' => 0 ] ] ) ;

        $this->assertSame( 0 , $out[ '_key' ] ) ;
        $this->assertSame( 0 , $out[ '_rev' ] ) ;
        $this->assertSame( 0 , $out[ 'n' ] ) ;
    }

    public function testMaskDocumentProtectsCustomAttributes() :void
    {
        // A non-ArangoDB model can protect its own identity fields.
        $doc = [ 'id' => 7 , '_key' => 'k' , 'amount' => 5 ] ;
        $out = maskDocument( $doc , [ [ 'path' => '*' , 'type' => 'integer' , 'lower' => 0 , 'upper' => 0 ] ] , [ 'id' ] ) ;

        $this->assertSame( 7 , $out[ 'id' ] ) ;     // protected
        $this->assertSame( 0 , $out[ '_key' ] ) ;   // NOT protected here (not in the custom list)
        $this->assertSame( 0 , $out[ 'amount' ] ) ;
    }

    public function testMaskDocumentFirstMatchingRuleWins() :void
    {
        $doc = [ 'address' => 'topsecret' ] ;
        $out = maskDocument( $doc ,
        [
            [ 'path' => 'address' , 'type' => 'xifyFront' , 'unmaskedLength' => 2 ] ,
            [ 'path' => '.address' , 'type' => 'email' ] ,
        ] ) ;
        $this->assertSame( 'xxxxxxxet' , $out[ 'address' ] ) ; // xifyFront won, not email
    }

    public function testMaskDocumentBacktickQuotedLiteralKey() :void
    {
        $doc = [ 'a.b' => 'topsecret' , 'plain' => 'keepme' ] ;
        $out = maskDocument( $doc , [ [ 'path' => '`a.b`' , 'type' => 'xifyFront' , 'unmaskedLength' => 2 ] ] ) ;
        $this->assertSame( 'xxxxxxxet' , $out[ 'a.b' ] ) ;
        $this->assertSame( 'keepme' , $out[ 'plain' ] ) ;   // backtick rule does not match this leaf
    }

    public function testMaskDocumentAcuteAccentQuotedLiteralKey() :void
    {
        $doc = [ 'a.b' => 'topsecret' , 'plain' => 'keepme' ] ;
        $out = maskDocument( $doc , [ [ 'path' => '´a.b´' , 'type' => 'xifyFront' , 'unmaskedLength' => 2 ] ] ) ;
        $this->assertSame( 'xxxxxxxet' , $out[ 'a.b' ] ) ;
        $this->assertSame( 'keepme' , $out[ 'plain' ] ) ;   // acute-accent rule does not match this leaf
    }

    public function testMaskDocumentArrayWithoutRuleIsLeftAlone() :void
    {
        $doc = [ 'tags' => [ 'x' , 'y' ] , 'email' => 'a@b.c' ] ;
        $out = maskDocument( $doc , [ [ 'path' => 'email' , 'type' => 'email' ] ] ) ;
        $this->assertSame( [ 'x' , 'y' ] , $out[ 'tags' ] ) ;          // untouched (no rule)
        $this->assertStringEndsWith( '.invalid' , $out[ 'email' ] ) ;
    }

    public function testMaskDocumentRuleWithoutTypeThrows() :void
    {
        $this->expectException( InvalidArgumentException::class ) ;
        $this->expectExceptionMessage( 'has no type' ) ;
        maskDocument( [ 'email' => 'x' ] , [ [ 'path' => 'email' ] ] ) ;
    }

    public function testMaskDocumentRecursesIntoNestedLists() :void
    {
        // 'groups' has no matching rule, so the array is walked deeper; one of its
        // elements is itself a list (a list-of-lists), exercising the recursion.
        $doc = [ 'groups' => [ [ [ 'name' => 'hugo' ] ] , 'egon' ] ] ;
        $out = maskDocument( $doc , [ [ 'path' => '.name' , 'type' => 'xifyFront' , 'unmaskedLength' => 2 ] ] ) ;

        $this->assertSame( 'xxgo' , $out[ 'groups' ][ 0 ][ 0 ][ 'name' ] ) ; // reached through the nested list
        $this->assertSame( 'egon' , $out[ 'groups' ][ 1 ] ) ;               // bare scalar untouched
    }

    // ------------------------------------------------------------------ extracted helpers (standalone)

    public function testResolveMaskingRuleStandalone() :void
    {
        $rules = [ [ 'path' => 'person.name' , 'type' => 'xifyFront' ] ] ;

        $this->assertSame( $rules[ 0 ] , resolveMaskingRule( $rules , 'name' , 'person.name' ) ) ;
        $this->assertNull( resolveMaskingRule( $rules , 'name' , 'other.name' ) ) ;

        $this->assertNotNull( resolveMaskingRule( [ [ 'path' => '*' , 'type' => 'random' ] ] , 'whatever' , 'a.b' ) ) ;
        $this->assertNotNull( resolveMaskingRule( [ [ 'path' => '.name' , 'type' => 'random' ] ] , 'name' , null ) ) ;
        $this->assertNotNull( resolveMaskingRule( [ [ 'path' => '`a.b`' , 'type' => 'random' ] ] , 'x' , 'a.b' ) ) ;
        $this->assertNull( resolveMaskingRule( [ [ 'path' => '`a.b`' , 'type' => 'random' ] ] , 'x' , 'other' ) ) ; // quoted literal, no match

        // Acute-accent quotes (´…´) are accepted as an alternative to backticks (multibyte-aware).
        $this->assertNotNull( resolveMaskingRule( [ [ 'path' => '´a.b´' , 'type' => 'random' ] ] , 'x' , 'a.b' ) ) ;
        $this->assertNull( resolveMaskingRule( [ [ 'path' => '´a.b´' , 'type' => 'random' ] ] , 'x' , 'other' ) ) ;

        // An exact-path rule cannot match once an array has been crossed (exactPath = null).
        $this->assertNull( resolveMaskingRule( [ [ 'path' => 'a.b' , 'type' => 'random' ] ] , 'b' , null ) ) ;
    }

    public function testMaskDocumentNodeStandalone() :void
    {
        // A protected attribute survives even a wildcard rule at depth 0.
        $out = maskDocumentNode
        (
            [ '_key' => 'a' , 'email' => 'real@example.com' ] ,
            [ [ 'path' => '*' , 'type' => 'email' ] ] ,
            '' ,
            0 ,
            [ '_key' ] ,
        ) ;

        $this->assertSame( 'a' , $out[ '_key' ] ) ;
        $this->assertStringEndsWith( '.invalid' , $out[ 'email' ] ) ;
    }

    public function testMaskDocumentListStandalone() :void
    {
        $out = maskDocumentList
        (
            [ [ 'name' => 'hugo' ] , 'egon' ] ,
            [ [ 'path' => '.name' , 'type' => 'xifyFront' , 'unmaskedLength' => 2 ] ] ,
            1 ,
        ) ;

        $this->assertSame( 'xxgo' , $out[ 0 ][ 'name' ] ) ;
        $this->assertSame( 'egon' , $out[ 1 ] ) ;
    }
}
