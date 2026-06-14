# Tests & couverture

`oihana/php-masking` est couverte par [PHPUnit 12](https://phpunit.de/), exécuté en **mode strict**.

## Lancer la suite

```bash
composer test            # exécute la suite unitaire
./vendor/bin/phpunit --filter MaskingsTest   # un seul cas de test
```

## Mesurer la couverture

La couverture nécessite **Xdebug** ou **PCOV**.

```bash
composer coverage        # texte + Clover + HTML sous build/coverage/
composer coverage:md     # résumé Markdown lisible (build/coverage/COVERAGE.md)
```

`composer coverage:md` convertit le rapport Clover en `build/coverage/COVERAGE.md` et tient un petit journal de tendance local (`build/coverage/history.json`), de sorte que chaque exécution affiche l'écart depuis la précédente.

Toute la bibliothèque est à **100 % de couverture de lignes**.

> `build/coverage/` est **ignoré par git** — un instantané de couverture devient obsolète au commit suivant, on le régénère donc à la demande plutôt que de le committer.

## Mode strict

`phpunit.xml` fait échouer l'exécution sur les avertissements, les tests risqués (sans assertion) et les tests sautés :

```
failOnRisky="true"  failOnWarning="true"  failOnSkipped="true"  failOnIncomplete="true"
```

Un test qui ne vérifie rien ne protège rien.

## Tester du code aléatoire

Chaque masker est **aléatoire** — la même entrée produit une sortie différente à chaque exécution. Les tests vérifient donc la **forme** et les **invariants** du résultat, jamais une valeur figée :

- **longueur** — `maskPhone('+31 6A-77')` conserve la même longueur et les caractères non-alphanumériques à leur place ;
- **classes de caractères** — un chiffre reste un chiffre, une majuscule reste une majuscule ;
- **bornes** — `maskInteger(…, 0, 10)` atterrit dans `[0, 10]` ;
- **invariants** — `maskCreditCard()` satisfait toujours la somme de contrôle de Luhn ;
- **anonymisation** — `maskEmail('real.person@…')` ne contient jamais la partie locale d'origine.

Quelques maskers sont assez déterministes pour être figés exactement — p. ex. `maskXifyFront('This is a test!Do you agree?')` est comparé à sa sortie de référence exacte.

## La politique `@codeCoverageIgnore`

Testez tout ce qui est atteignable. N'annotez une ligne `@codeCoverageIgnore` que lorsqu'elle est véritablement impossible à atteindre depuis la surface publique (une garde défensive qu'aucune entrée ne peut déclencher). La bibliothèque n'en a actuellement **aucune**.

## Une note sur la stabilité du comportement

Quand vous découvrez un comportement surprenant dans du code existant, **figez-le d'abord dans un test**. Ne changez pas le comportement d'une fonction publique sans en discuter : d'autres bibliothèques — notamment [`oihana/php-arango`](https://github.com/BcommeBois/oihana-php-arango), qui consomme ce moteur — peuvent en dépendre.

## Et ensuite ?

- [Masquer un document](guide/documents.md) — le moteur que ces tests couvrent.
- Retour au [sommaire français](README.md).
