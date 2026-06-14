# Le catalogue des maskers

Un **masker** est une transformation unique, identifiée par un nom. Les 10 maskers vivent dans l'espace de noms `oihana\masking`, une fonction par fichier, et sont aussi exposés comme constantes de l'énumération [`Masker`](../../../src/oihana/masking/enums/Masker.php).

On appelle rarement une fonction de masker directement — la plupart du temps, on la nomme dans une règle (`'type' => 'email'`) et l'on laisse [`maskValue()`](values.md) ou [`maskDocument()`](documents.md) faire la répartition. Mais chaque masker est une fonction autonome et publique que vous *pouvez* appeler seule.

> **Tous les maskers sont aléatoires.** Les exemples ci-dessous montrent la *forme* d'une sortie possible, pas une valeur figée. On ne peut jamais retrouver l'original à partir du résultat.

## Vue d'ensemble

| `type` | Fonction | Remplace par | Paramètres clés |
|---|---|---|---|
| `creditCard` | `maskCreditCard()` | un nombre à 16 chiffres aléatoire, valide selon Luhn (`int`) | — |
| `datetime` | `maskDatetime()` | un instant aléatoire dans `[begin, end]`, formaté | `begin`, `end`, `format` |
| `decimal` | `maskDecimal()` | un décimal aléatoire dans `[lower, upper]` (`float`) | `lower`, `upper`, `scale` |
| `email` | `maskEmail()` | une adresse `AAAA.BBBB@CCCC.invalid` aléatoire | — |
| `integer` | `maskInteger()` | un entier aléatoire dans `[lower, upper]` | `lower`, `upper` |
| `phone` | `maskPhone()` | un numéro de même forme (chiffre→chiffre, lettre→lettre) | `default` |
| `random` | `maskRandom()` | une valeur aléatoire du **même type** | — |
| `randomString` | `maskRandomString()` | une chaîne aléatoire de même longueur (chaînes uniquement) | — |
| `xifyFront` | `maskXifyFront()` | le début de chaque mot remplacé par `x` | `unmaskedLength`, `hash`, `seed` |
| `zip` | `maskZip()` | un code postal de même forme | `default` |

## Référence

### `creditCard`

Renvoie un nombre à 16 chiffres dont la clé de contrôle satisfait l'[algorithme de Luhn](https://fr.wikipedia.org/wiki/Formule_de_Luhn). La valeur d'origine est ignorée.

```php
use function oihana\masking\maskCreditCard;

maskCreditCard( '4111-1111-1111-1111' ); // p. ex. 4143300214110028 (int)
```

### `datetime`

Tire un instant aléatoire dans `[begin, end]` et le rend avec `format`, à l'aide de jetons de style `DATE_FORMAT()` : `%yyyy`, `%yy`, `%mm`, `%m`, `%dd`, `%d`, `%hh`, `%h`, `%ii`, `%i`, `%ss`, `%s`, `%fff`, `%%`. Quand `format` est vide (par défaut), une **chaîne vide** est renvoyée. Des bornes dans le mauvais ordre sont permutées ; des bornes illisibles retombent sur l'époque / l'instant courant.

```php
use function oihana\masking\maskDatetime;

maskDatetime( null , '2019-01-01' , '2019-12-31' , '%yyyy-%mm-%dd' ); // p. ex. "2019-06-17"
maskDatetime( null );                                                 // "" (pas de format)
```

| Paramètre | Défaut | Signification |
|---|---|---|
| `begin` | `1970-01-01T00:00:00.000` | Instant le plus ancien (ISO 8601). |
| `end` | `''` → maintenant | Instant le plus récent (ISO 8601). |
| `format` | `''` → renvoie `""` | Le motif de sortie. |

### `decimal`

Remplace la valeur — **quel que soit son type d'origine** — par un décimal aléatoire dans `[lower, upper]`, arrondi à `scale` décimales. Bornes inclusives et permutées si inversées ; un `scale` négatif est ramené à 0.

```php
use function oihana\masking\maskDecimal;

maskDecimal( 3.14 );                 // p. ex. -0.42 (défaut -1..1, scale 2)
maskDecimal( 'x' , -0.3 , 0.3 , 2 ); // p. ex. 0.17
```

| Paramètre | Défaut | Signification |
|---|---|---|
| `lower` | `-1.0` | Plus petite valeur. |
| `upper` | `1.0` | Plus grande valeur. |
| `scale` | `2` | Nombre max de décimales. |

### `email`

Renvoie une adresse `AAAA.BBBB@CCCC.invalid` aléatoire. Le TLD `.invalid` est réservé (RFC 2606) et ne se résout jamais. La valeur d'origine n'est jamais reflétée.

```php
use function oihana\masking\maskEmail;

maskEmail( 'real.person@example.com' ); // p. ex. "x7Bq.9aMz@Kp3R.invalid"
```

### `integer`

Remplace la valeur — **quel que soit son type d'origine** — par un entier aléatoire dans `[lower, upper]` (inclusif, permuté si inversé).

```php
use function oihana\masking\maskInteger;

maskInteger( 9999 );         // p. ex. 42  (défaut -100..100)
maskInteger( 'x' , 0 , 10 ); // p. ex. 7
```

| Paramètre | Défaut | Signification |
|---|---|---|
| `lower` | `-100` | Plus petite valeur. |
| `upper` | `100` | Plus grande valeur. |

### `phone`

Remplace chaque **chiffre** par un chiffre aléatoire et chaque **lettre** par une lettre aléatoire (casse conservée) ; tout autre caractère est laissé tel quel. Les valeurs non-chaîne utilisent le repli `default`.

```php
use function oihana\masking\maskPhone;

maskPhone( '+31 66-77-88' ); // p. ex. "+75 10-79-52"
maskPhone( 1234 );           // "+1234567890" (défaut, non-chaîne)
```

| Paramètre | Défaut | Signification |
|---|---|---|
| `default` | `'+1234567890'` | Repli quand la valeur n'est pas une chaîne. |

### `random`

Remplace une feuille par une valeur aléatoire de **même nature** : chaînes → une chaîne aléatoire ; entiers → `[-1000, 1000]` ; flottants → un décimal dans `[-1000, 1000]` ; booléens → un booléen aléatoire ; `null` reste `null`.

```php
use function oihana\masking\maskRandom;

maskRandom( 'hello' ); // p. ex. "x7Bqz"
maskRandom( 42 );      // p. ex. -738
maskRandom( true );    // p. ex. false
maskRandom( null );    // null
```

### `randomString`

Comme `random`, mais **seules les chaînes sont modifiées** — tout autre type est renvoyé tel quel. Le remplacement conserve la longueur d'origine (min. 1).

```php
use function oihana\masking\maskRandomString;

maskRandomString( 'My Name' ); // p. ex. "x7Bqz9a"
maskRandomString( 1234 );      // 1234 (inchangé)
```

### `xifyFront`

Dans chaque **mot** (une suite de caractères alphanumériques, `_` ou `-`), chaque caractère sauf les `unmaskedLength` derniers est remplacé par `x` ; les mots qui ne dépassent pas `unmaskedLength` sont laissés tels quels. Tout autre caractère devient une espace. Les valeurs non-chaîne deviennent la chaîne fixe `"xxxx"` ; `null` reste `null`. Avec `hash = true`, un hachage de 8 caractères de l'entrée (salé par `seed`) est ajouté pour réduire les collisions.

```php
use function oihana\masking\maskXifyFront;

maskXifyFront( 'This is a test!Do you agree?' ); // "xxis is a xxst Do xou xxxee "
maskXifyFront( 'secret' , 3 );                   // "xxxret"
maskXifyFront( true );                           // "xxxx"
maskXifyFront( null );                           // null
```

| Paramètre | Défaut | Signification |
|---|---|---|
| `unmaskedLength` | `2` | Caractères de fin de chaque mot à conserver. |
| `hash` | `false` | Ajoute un court hachage. |
| `seed` | `0` | Secret utilisé par le hachage. |

### `zip`

Même brouillage préservant la forme que `phone`, pour les codes postaux.

```php
use function oihana\masking\maskZip;

maskZip( '50674' );   // p. ex. "98146"
maskZip( 'SA34-EA' ); // p. ex. "OW91-JI"
maskZip( null );      // "12345" (défaut)
```

| Paramètre | Défaut | Signification |
|---|---|---|
| `default` | `'12345'` | Repli quand la valeur n'est pas une chaîne. |

## Et ensuite ?

- [Masquer une valeur](values.md) — le répartiteur `maskValue()`.
- [Masquer un document](documents.md) — appliquer des règles à un document imbriqué complet.
