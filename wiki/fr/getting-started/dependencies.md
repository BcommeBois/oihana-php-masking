# Dépendances

`oihana/php-masking` est volontairement **légère**. Elle ne déclare qu'une seule dépendance d'exécution `oihana/*`.

## Dépendances d'exécution

| Paquet | Version | Pourquoi |
|---|---|---|
| `php` | `>=8.4` | Énumérations, arguments nommés, système de types moderne. |
| `ext-ctype` | `*` | Tests de classe de caractères (`ctype_digit`, `ctype_alpha`, `ctype_upper`) dans `maskPhone`, `maskZip`, `maskXifyFront`. Livrée avec PHP. |
| [`oihana/php-reflect`](https://github.com/BcommeBois/oihana-php-reflect) | `dev-main` | Fournit `oihana\reflect\traits\ConstantsTrait`, utilisé par les énumérations `Masker` et `MaskingMode` pour exposer `getAll()` et consorts. |

C'est **toute** la surface d'exécution. Les fonctions de masquage elles-mêmes ne reposent que sur des primitives PHP (`random_int`, `array_map`, `ctype_*`, `gmdate`, `md5`, …).

### Pourquoi `oihana/php-reflect` ?

Les deux énumérations (`Masker`, `MaskingMode`) sont de simples classes portant des valeurs `const string`. Elles font `use ConstantsTrait` afin que, par exemple, `Masker::getAll()` renvoie la liste des noms de maskers valides — que `maskValue()` utilise pour construire un message d'erreur utile lorsqu'un masker inconnu est demandé :

```php
throw new InvalidArgumentException(
    sprintf( "Unknown masker '%s'. Valid maskers: %s." , $type , implode( ', ' , Masker::getAll() ) )
);
```

`oihana/php-reflect` entraîne `oihana/php-core` de façon transitive ; les deux sont des briques `oihana/*` stables, partagées dans tout l'écosystème.

## Dépendances de développement

| Paquet | Version | Pourquoi |
|---|---|---|
| `phpunit/phpunit` | `^12` | La suite de tests unitaires. |
| `nunomaduro/collision` | `^8.8` | Affichage soigné des erreurs en console pendant les tests. |
| `phpdocumentor/shim` | `^3.8` | Exécute phpDocumentor via `composer doc`. |

## Stabilité

Le `composer.json` racine fixe `"minimum-stability": "dev"` avec `"prefer-stable": true`, car les paquets `oihana/*` sont suivis depuis leur branche `dev-main`. Les paquets tiers stables (PHPUnit, …) sont quant à eux résolus vers leurs versions taguées.

## Et ensuite ?

- [Le catalogue des maskers](../guide/maskers.md) — les 10 maskers.
- [Installation](installation.md) — retour aux étapes d'installation.
