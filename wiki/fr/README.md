# oihana/php-masking — Boîte à outils de masquage de données pour PHP

![Langue](https://img.shields.io/badge/langue-Français-blue)

`oihana/php-masking` est une bibliothèque PHP 8.4+ qui **anonymise et caviarde** les champs de vos documents. Le code est organisé en **fonctions autonomes composables**, autochargées via `composer.autoload.files`, avec des **énumérations fortement typées** à la place des *chaînes magiques*.

![Oihana PHP Masking](https://raw.githubusercontent.com/BcommeBois/oihana-php-masking/main/assets/images/oihana-php-masking-logo-inline-512x160.png)

## À qui s'adresse cette documentation

Aux développeurs PHP qui doivent **supprimer ou brouiller des données sensibles** (données personnelles) avant qu'elles ne franchissent une frontière de confiance :

- produire des **extractions de base anonymisées** pour la pré-production ou le développement local ;
- générer des **jeux d'essai** et des **données de test** réalistes mais sans rien révéler ;
- caviarder des **journaux** ou des **exports** pour la conformité RGPD / vie privée.

## Démarrage rapide

```php
use function oihana\masking\maskDocument;

$document =
[
    '_key'  => '42',
    'name'  => 'Jane Doe',
    'email' => 'jane.doe@example.com',
    'phone' => '+33 6 12 34 56 78',
];

$rules =
[
    [ 'path' => 'name'  , 'type' => 'xifyFront' , 'unmaskedLength' => 2 ],
    [ 'path' => 'email' , 'type' => 'email' ],
    [ 'path' => 'phone' , 'type' => 'phone' ],
];

$masked = maskDocument( $document , $rules );
// _key reste '42' ; name -> "xxxx xxe" ; email -> "aZ12.bY34@cX56.invalid" ; phone -> aléatoire de même forme.
```

## Sommaire

### Démarrage — [`getting-started/`](getting-started/)

- [Introduction](getting-started/introduction.md) — ce que fait la bibliothèque, la philosophie *oihana*, et pourquoi elle existe.
- [Installation](getting-started/installation.md) — prérequis PHP 8.4+, la commande `composer require`, vérification post-installation.
- [Dépendances](getting-started/dependencies.md) — `oihana/php-reflect` et son rôle.

### Guide — [`guide/`](guide/)

- [Le catalogue des maskers](guide/maskers.md) — les 10 maskers (`email`, `phone`, `creditCard`, `datetime`, `decimal`, `integer`, `zip`, `random`, `randomString`, `xifyFront`), leurs paramètres et la forme de sortie.
- [Masquer une valeur](guide/values.md) — le répartiteur `maskValue()` et la règle « élément par élément ».
- [Masquer un document](guide/documents.md) — le moteur `maskDocument()`, le **DSL de chemins**, les attributs système protégés, la priorité des règles.

### Transversal

- [Tests & couverture](testing.md) — lancer la suite PHPUnit, mesurer la couverture, et la politique `@codeCoverageIgnore`.

## Code source

Le code vit sous [`src/oihana/masking/`](../../src/oihana/masking/) — espace de noms `oihana\masking` (les fonctions) et `oihana\masking\enums` (les énumérations `Masker` et `MaskingMode`).

## Voir aussi

- [Packagist `oihana/php-masking`](https://packagist.org/packages/oihana/php-masking) — la page du paquet.
- [Référence d'API (phpDocumentor)](https://bcommebois.github.io/oihana-php-masking) — référence générée au niveau des fonctions.
- [`oihana/php-arango`](https://github.com/BcommeBois/oihana-php-arango) — la boîte à outils ArangoDB d'où ce moteur a été extrait.
