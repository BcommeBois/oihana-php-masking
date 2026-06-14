# Installation

## Prérequis

### Version de PHP

`oihana/php-masking` requiert **PHP 8.4 ou supérieur**. La bibliothèque s'appuie sur des fonctionnalités modernes :

- **Énumérations** (PHP 8.1+) — `Masker`, `MaskingMode`.
- **Arguments nommés** (PHP 8.0+) — passer explicitement les paramètres d'un masker.
- **Visibilité asymétrique et accesseurs de propriété** (PHP 8.4+) — utilisés par `oihana/php-reflect` (dépendance transitive).

Vérification :

```bash
php -v
# PHP 8.4.x (cli) (built: ...)
```

Si votre version est plus ancienne, mettez PHP à jour via votre gestionnaire de paquets (`brew install php@8.4`, `apt install php8.4`, etc.).

### Extensions PHP requises

| Extension | Rôle dans `oihana/php-masking` |
|---|---|
| `ext-ctype` | Tests de classe de caractères (`ctype_digit`, `ctype_alpha`, `ctype_upper`) utilisés par `maskPhone`, `maskZip`, `maskXifyFront`. Livrée avec PHP par défaut. |

> Les maskers utilisent `random_int()` (cryptographiquement sûr), actif par défaut — aucune extension à installer.

## Installation via Composer

> Nécessite [Composer](https://getcomposer.org/) ≥ 2.0.

```bash
composer require oihana/php-masking
```

Cette commande installe automatiquement `oihana/php-reflect` (voir [Dépendances](dependencies.md)).

### Installation pour le développement

Pour contribuer ou exécuter la suite de tests en local :

```bash
git clone https://github.com/BcommeBois/oihana-php-masking.git
cd oihana-php-masking
composer install
```

## Vérification post-installation

Créez un fichier `test.php` à la racine de votre projet :

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use function oihana\masking\maskValue;
use function oihana\masking\maskDocument;

echo maskValue( 'xifyFront' , 'secret' , [ 'unmaskedLength' => 3 ] ) , PHP_EOL ;
// xxxret

print_r( maskDocument(
    [ '_key' => 'a' , 'email' => 'real@example.com' ] ,
    [ [ 'path' => 'email' , 'type' => 'email' ] ]
) ) ;
// Array ( [_key] => a [email] => aZ12.bY34@cX56.invalid )
```

```bash
php test.php
```

Si la sortie correspond aux formes ci-dessus, l'autochargement `composer.autoload.files` fonctionne et la bibliothèque est opérationnelle.

## Lancer la suite de tests (installation dev uniquement)

`oihana/php-masking` est couverte par [PHPUnit 12](https://phpunit.de/) :

```bash
composer test
```

Pour mesurer la couverture (nécessite Xdebug ou PCOV) :

```bash
composer coverage        # texte + Clover + HTML sous build/coverage/
composer coverage:md     # résumé Markdown lisible (build/coverage/COVERAGE.md)
```

La configuration se trouve dans `phpunit.xml`, à la racine du projet.

## Générer la référence phpDocumentor

```bash
composer doc
```

Cette commande nettoie puis régénère `docs/` (sortie HTML). À ne pas confondre avec **ce wiki**, qui vit sous `wiki/` et est du Markdown écrit à la main en FR/EN.

## Et ensuite ?

- [Dépendances](dependencies.md) — ce que fournit `oihana/php-reflect`.
- [Le catalogue des maskers](../guide/maskers.md) — les 10 maskers.
- [Introduction](introduction.md) — retour à la vue d'ensemble.
