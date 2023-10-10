# kotoba

_japanese flashcards!_

## Stack

Les projets open source suivants donnent vie à kotoba :

- [API Platform]
- [mongoDB]
- [PHPUnit]

#### Dépendances dev

- [CaptainHook] - gestion des hooks git
- [PHPStan] - analyse statique de code
- [PHP CS Fixer] - standard PSR-12

## Documentation OpenAPI

http://localhost:8000/api

## Installation

```sh
composer i
```

## Scripts

#### Serveur de dev

```sh
symfony serve
```

#### Tests

```sh
composer test
```

#### Lint

Execute PHPStan puis PHP CS Fixer

```sh
composer lint
```

#### Hooks git

Le hook pre-commit est configuré pour lancer les tests et linter le code. Le commit sera rejeté si l'un des cas suivants se produit :
- l'un des tests unitaires ne passe pas
- ou les linters PHPStan et PHP CS Fixer retournent une erreur
- ou encore le message de commit ne suivrait pas le style [Conventional Commits]

Pour lancer manuellement ce script du hook :

```sh
vendor/bin/captainhook hook:pre-commit
```

Pour bypasser le hook et toutes les vérifications mentionnées plus haut :

```sh
git commit -m 'message' --no-verify
```

[API Platform]: https://api-platform.com/
[mongodb]: https://www.mongodb.com/
[PHPUnit]: https://phpunit.de/
[CaptainHook]: https://captainhookphp.github.io/captainhook/
[PHPStan]: https://phpstan.org/
[PHP CS Fixer]: https://cs.symfony.com/
[Conventional Commits]: https://www.conventionalcommits.org/en/v1.0.0/

_RAVINDRA Soudakar - 2023_