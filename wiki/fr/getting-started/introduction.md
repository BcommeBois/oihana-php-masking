# Introduction

## Ce que fait `oihana/php-masking`

`oihana/php-masking` est une **boîte à outils PHP 8.4+** qui **anonymise et caviarde** les champs d'un document. Donnez-lui une valeur (ou un document imbriqué complet) et un jeu de règles : elle renvoie une copie où les parties sensibles sont remplacées par des données réalistes mais fausses.

Elle répond à un besoin récurrent : **partager ou réutiliser des données sans divulguer d'informations personnelles** —

- une **extraction anonymisée** de la production pour tester en pré-production ou en local ;
- des **jeux d'essai** et des **données de test** qui conservent la forme et les statistiques des vraies données sans exposer qui que ce soit ;
- des **journaux** ou des **exports** caviardés pour la conformité RGPD / vie privée.

Le code ne définit *aucune classe monolithique* : c'est un ensemble de **17 fonctions autonomes**, chacune dans son fichier, autochargées via `composer.autoload.files`, plus deux **énumérations fortement typées** (`Masker`, `MaskingMode`).

## La philosophie *oihana*

Trois principes traversent toute la bibliothèque — et plus largement l'écosystème `oihana/*` :

1. **Des fonctions composables, sans cadriciel lourd.** Chaque utilitaire est une fonction PHP autochargeable. On appelle `maskDocument()`, ou l'on compose `maskValue()` soi-même, plutôt que d'instancier un `MaskingEngine` et d'enchaîner ses méthodes. Si vous savez lire une signature de fonction, vous savez utiliser la bibliothèque.

2. **Zéro *chaîne magique*.** Les noms de maskers (`'email'`, `'xifyFront'`, …) sont exposés comme constantes de l'énumération `Masker` ; les modes au niveau collection (`'masked'`, `'exclude'`, …) comme constantes de `MaskingMode`. Les renommages sont sûrs, l'autocomplétion fonctionne, et une faute de frappe est détectée immédiatement.

3. **Anonymisation, pas chiffrement réversible.** Les maskers remplacent les données par des valeurs **aléatoires** de même nature / forme. Ce ne sont *ni* un hachage réversible *ni* un chiffrement — le but est qu'on ne puisse pas retrouver la valeur d'origine à partir du résultat.

## Pourquoi une bibliothèque dédiée

Ce moteur est né au sein d'[`oihana/php-arango`](https://github.com/BcommeBois/oihana-php-arango), pour post-traiter les fichiers JSON d'une extraction de base. Il est vite apparu que la logique de masquage elle-même est **indépendante de la base** : elle travaille sur de simples tableaux PHP, et ignore tout d'ArangoDB, d'AQL ou de tout pilote.

L'extraire dans `oihana/php-masking`, c'est :

- pouvoir la réutiliser **partout** — n'importe quel cadriciel, n'importe quelle source, n'importe quelle sortie ;
- ne porter **qu'une seule dépendance légère** (`oihana/php-reflect`, pour le trait des énumérations) ;
- permettre à `oihana/php-arango` de la **consommer** au lieu de la dupliquer.

## Le vocabulaire

Quelques termes reviennent tout au long de cette documentation :

- **Masker** — une transformation unique, identifiée par un nom (`email`, `phone`, `creditCard`, …). Voir le [catalogue des maskers](../guide/maskers.md).
- **Règle** — un tableau `{ 'path' => …, 'type' => <masker>, …paramètres }` qui indique *quelles* feuilles masquer et *comment*.
- **Feuille** — une valeur qui est `null`, un scalaire ou un tableau JSON. Les objets (tableaux associatifs) sont parcourus en profondeur, pas masqués directement.
- **Chemin (`path`)** — le localisateur dans une règle. Les formes acceptées (exact, nom à n'importe quelle profondeur, joker, littéral entre accents graves) forment le **DSL de chemins** — voir [Masquer un document](../guide/documents.md).
- **Attributs protégés** — les noms d'attributs de premier niveau que vous demandez au moteur de ne jamais masquer (défaut : aucun). Vous fournissez les champs d'identité de votre propre modèle (p. ex. `['_key', '_id', ...]` pour ArangoDB, `['_id']` pour MongoDB) — la bibliothèque ne code rien en dur.

## Public et prérequis

Cette documentation suppose que le lecteur maîtrise **PHP 8.4+** (énumérations, arguments nommés) et est à l'aise avec **Composer** et son mécanisme `autoload.files`. Aucune connaissance préalable des autres bibliothèques `oihana/*` n'est requise.

## Et ensuite ?

- [Installation](installation.md) — installer la bibliothèque et vérifier qu'elle fonctionne.
- [Dépendances](dependencies.md) — le rôle d'`oihana/php-reflect`.
- [Le catalogue des maskers](../guide/maskers.md) — commencer à masquer.

Pour l'index complet, retour au [sommaire français](../README.md).
