# Masquer un document

[`maskDocument()`](../../../src/oihana/masking/maskDocument.php) est le moteur. Il prend un **document** (un objet JSON décodé, c.-à-d. un tableau associatif) et une liste de **règles**, et renvoie une copie masquée.

```php
use function oihana\masking\maskDocument;

maskDocument( array $doc , array $maskings ) : array
```

Chaque règle est un tableau :

```php
[ 'path' => <chemin> , 'type' => <masker> , ...paramètres ]
```

- `path` — *quelles* feuilles masquer (voir le [DSL de chemins](#le-dsl-de-chemins) ci-dessous) ;
- `type` — *comment* les masquer (un nom de masker ; voir le [catalogue](maskers.md)) ;
- toute clé supplémentaire est transmise au masker comme paramètre (`unmaskedLength`, `lower`, `format`, …).

## Un premier exemple

```php
use function oihana\masking\maskDocument;

$doc =
[
    '_key'    => 'a',
    'email'   => 'real@example.com',
    'profile' => [ 'name' => 'Jane' ],
];

$out = maskDocument( $doc,
[
    [ 'path' => 'email' , 'type' => 'email' ],
    [ 'path' => '.name' , 'type' => 'xifyFront' ],
]);
// [ '_key' => 'a' , 'email' => 'aZ12.bY34@cX56.invalid' , 'profile' => [ 'name' => 'xxne' ] ]
```

Une **liste de règles vide** renvoie le document intact.

## Comment le moteur parcourt le document

- Une **feuille** — une valeur `null`, un scalaire ou un tableau JSON (liste) — est candidate au masquage.
- Un **objet** (tableau associatif) est **parcouru en profondeur**, jamais masqué dans son ensemble.
- Quand une feuille filtrée est elle-même un tableau, le masker est appliqué à ses éléments individuellement (voir [Masquer une valeur](values.md#les-tableaux-sont-masqués-élément-par-élément)).
- Une feuille de type tableau **sans règle correspondante** est parcourue plus profondément, afin qu'une règle puisse tout de même atteindre des objets imbriqués à l'intérieur.

## Le DSL de chemins

Le `path` d'une règle sélectionne les feuilles auxquelles elle s'applique. Cinq formes sont acceptées :

| Forme | Correspond à | Exemple |
|---|---|---|
| `"name"` | une feuille `name` au **premier niveau** | `'email'` |
| `"a.b"` | le chemin imbriqué **exact** `a` → `b` (à travers des objets seulement) | `'profile.zip'` |
| `".name"` | **toute** feuille nommée `name`, à **n'importe quelle profondeur** | `'.address'` |
| `"*"` | **toute** feuille | `'*'` |
| `` "`a.b`" `` | un nom d'attribut **littéral** contenant des points (entre accents graves) | `` '`user.id`' `` |

### Chemin exact

```php
$doc = [ 'person' => [ 'name' => 'foobar' ] , 'other' => [ 'name' => 'kepterm' ] ];
maskDocument( $doc , [ [ 'path' => 'person.name' , 'type' => 'xifyFront' , 'unmaskedLength' => 2 ] ]);
// person.name -> "xxxxar" ; other.name reste "kepterm" (le chemin exact ne correspond pas ailleurs)
```

### Nom à n'importe quelle profondeur (`.name`)

```php
$doc = [ 'name' => 'top' , 'nicknames' => [ [ 'name' => 'hugo' ] , 'egon' ] ];
maskDocument( $doc , [ [ 'path' => '.name' , 'type' => 'xifyFront' , 'unmaskedLength' => 2 ] ]);
// chaque feuille "name" est masquée, y compris celle imbriquée dans le tableau ;
// le scalaire nu "egon" est laissé tel quel (ce n'est pas un attribut nommé).
```

### Joker (`*`)

```php
$doc = [ '_key' => 'k' , 'n' => 5 , 'arr' => [ 1 , 2 ] , 'o' => [ 'x' => 9 ] ];
maskDocument( $doc , [ [ 'path' => '*' , 'type' => 'integer' , 'lower' => 0 , 'upper' => 0 ] ]);
// chaque feuille devient 0 — sauf l'attribut système _key, qui est préservé.
```

### Clé littérale entre accents graves

Quand un nom d'attribut contient lui-même un point, mettez-le entre accents graves pour que le moteur ne le lise pas comme un chemin imbriqué :

```php
$doc = [ 'a.b' => 'topsecret' , 'plain' => 'keepme' ];
maskDocument( $doc , [ [ 'path' => '`a.b`' , 'type' => 'xifyFront' , 'unmaskedLength' => 2 ] ]);
// la feuille "a.b" est masquée ; "plain" ne l'est pas.
```

## Priorité des règles — la première qui correspond gagne

Les règles sont évaluées **dans l'ordre de déclaration** ; la **première** qui correspond à une feuille est appliquée. Placez la règle la plus spécifique avant la plus large :

```php
$doc = [ 'address' => 'topsecret' ];
maskDocument( $doc,
[
    [ 'path' => 'address'  , 'type' => 'xifyFront' , 'unmaskedLength' => 2 ], // gagne
    [ 'path' => '.address' , 'type' => 'email' ],                            // jamais atteinte pour cette feuille
]);
// address -> "xxxxxxxet" (xifyFront), pas une adresse e-mail.
```

## Attributs système protégés

Les **attributs système** de premier niveau — `_key`, `_id`, `_rev`, `_from`, `_to` — ne sont **jamais** masqués, même par une règle `*`. Ils portent l'identité du document et les références d'arêtes, et doivent survivre intacts. La liste est fournie par [`maskingSystemAttributes()`](../../../src/oihana/masking/maskingSystemAttributes.php) :

```php
use function oihana\masking\maskingSystemAttributes;

maskingSystemAttributes(); // [ '_key' , '_id' , '_rev' , '_from' , '_to' ]
```

> La protection s'applique **au premier niveau uniquement** — un attribut imbriqué qui s'appellerait `_key` *est*, lui, éligible au masquage.

## Une règle sans `type` lève une exception

Chaque règle doit nommer un masker. Une règle dont le `type` manque (ou n'est pas une chaîne) lève une `InvalidArgumentException` :

```php
maskDocument( [ 'email' => 'x' ] , [ [ 'path' => 'email' ] ] );
// InvalidArgumentException : Masking rule for path 'email' has no type.
```

## Les fonctions de plus bas niveau

`maskDocument()` est bâti sur trois fonctions publiques que vous pouvez aussi appeler directement pour un contrôle plus fin :

- [`maskDocumentNode()`](../../../src/oihana/masking/maskDocumentNode.php) — parcourt un objet, masque les feuilles correspondantes et descend dans les objets/tableaux imbriqués. Signature : `maskDocumentNode( array $node , array $maskings , ?string $exactPath , int $depth )`.
- [`maskDocumentList()`](../../../src/oihana/masking/maskDocumentList.php) — applique les règles à chaque élément d'une liste.
- [`resolveMaskingRule()`](../../../src/oihana/masking/resolveMaskingRule.php) — renvoie la première règle correspondant à un nom d'attribut et un chemin exact donnés, ou `null`.

```php
use function oihana\masking\resolveMaskingRule;

$rules = [ [ 'path' => 'person.name' , 'type' => 'xifyFront' ] ];
resolveMaskingRule( $rules , 'name' , 'person.name' ); // la règle
resolveMaskingRule( $rules , 'name' , 'other.name' );  // null
```

## Et ensuite ?

- [Le catalogue des maskers](maskers.md) — chaque masker et ses paramètres.
- [Masquer une valeur](values.md) — le répartiteur `maskValue()`.
- [Tests & couverture](../testing.md) — comment le moteur est vérifié.
