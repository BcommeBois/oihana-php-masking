# Masquer une valeur

[`maskValue()`](../../../src/oihana/masking/maskValue.php) est le **répartiteur** placé devant les maskers par type. C'est ce que `maskDocument()` appelle en interne, et vous pouvez l'utiliser directement quand vous avez une valeur sous la main plutôt qu'un document complet.

```php
use function oihana\masking\maskValue;

maskValue( string $type , mixed $value , array $params = [] ) : mixed
```

- `$type` — un nom de masker (voir le [catalogue](maskers.md), ou les constantes de l'énumération `Masker`) ;
- `$value` — la valeur à masquer ;
- `$params` — les paramètres du masker (les mêmes clés que dans le catalogue).

## Exemples

```php
use function oihana\masking\maskValue;

maskValue( 'email' , 'real@example.com' );                       // "x7Bq.9aMz@Kp3R.invalid"
maskValue( 'xifyFront' , 'secret' , [ 'unmaskedLength' => 3 ] ); // "xxxret"
maskValue( 'integer' , 'x' , [ 'lower' => 0 , 'upper' => 10 ] ); // p. ex. 7
maskValue( 'phone' , 1 );                                        // "+1234567890" (repli non-chaîne)
```

En utilisant l'énumération pour éviter les *chaînes magiques* :

```php
use oihana\masking\enums\Masker;
use function oihana\masking\maskValue;

maskValue( Masker::CREDIT_CARD , null ); // p. ex. 4143300214110028
```

## Les tableaux sont masqués élément par élément

Quand `$value` est un **tableau JSON** (une *liste* PHP), le masker est appliqué à **chaque élément individuellement** :

- un élément scalaire est masqué directement ;
- une **liste** imbriquée fait une récursion ;
- un **objet** imbriqué (tableau associatif) est laissé tel quel — un masker au niveau valeur ne descend pas dans les objets.

```php
use function oihana\masking\maskValue;

$out = maskValue( 'integer' , [ 1 , [ 2 , 3 ] , [ 'o' => 'keep' ] , 'str' ] , [ 'lower' => 0 , 'upper' => 0 ] );
// [ 0 , [ 0 , 0 ] , [ 'o' => 'keep' ] , 0 ]
//   ↑    ↑           ↑                   ↑
//   |    sous-liste  objet imbriqué      scalaire masqué
//   scalaire masqué  (récursion)         intact
```

Cela reflète la règle courante « les éléments d'un tableau sont masqués individuellement », si bien que masquer une liste de numéros de téléphone ou une liste d'étiquettes fait ce qu'il faut sans configuration supplémentaire.

## Un masker inconnu lève une exception

Passer un nom qui n'est pas un masker connu lève une `InvalidArgumentException` listant les noms valides :

```php
maskValue( 'nope' , 'x' );
// InvalidArgumentException :
// Unknown masker 'nope'. Valid maskers: creditCard, datetime, decimal, email, integer, phone, random, randomString, xifyFront, zip.
```

## Quand utiliser `maskValue` plutôt que `maskDocument`

| Utilisez… | Quand… |
|---|---|
| `maskValue()` | vous avez déjà la ou les valeurs exactes à masquer et décidez vous-même *quel* masker appliquer. |
| [`maskDocument()`](documents.md) | vous avez un document complet et voulez des **règles par chemin** pour sélectionner les feuilles, descendre dans les objets/tableaux imbriqués, et protéger les attributs de votre choix. |

## Et ensuite ?

- [Masquer un document](documents.md) — le DSL de chemins et le moteur de documents.
- [Le catalogue des maskers](maskers.md) — chaque masker et ses paramètres.
