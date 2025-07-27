# GEMINI.md

This file provides guidance when working with code in this repository.

## Contexte

Ce document présente le cahier des charges général de l'application web "Unescopilot".
L'objectif est de créer une application légère et conviviale pour recenser, explorer et suivre les sites
du patrimoine mondial de l'UNESCO. L'application est destinée à un usage non commercial et met l'accent
sur la simplicité de développement et la facilité d'utilisation, avec une approche "entre amis".

## KISS

- This repository is a small personnal Symfony for a side project.
- Don't bother with complexity, it's a simple project.
- KISS is the key.
- Don't hesitate to ask for more details if needed.

## Language, Framework & tools

- **PHP**: 8.2+
- **Symfony**: 7.3+
- **Javascript**: vanilla, ES6+
- **AlpineJS**
- **Tailwind**

## Code quality tools
- 
- `php bin/console lint:yaml /config/xxx`: Lint YAML files
- `php vendor/bin/phpstan analyse -c phpstan.neon`: PhpStan static analysis
- `php bin/phpunit`: Run PHPUnit tests

## Ressources utiles

- [Cahier des charges](specifications.md)

## Comment travailler

Pour chaque tâche :
- Implémenter la fonctionnalité
- Écrire quelques tests unitaires (si pertinent) ou d'endpoint api
- Commiter avec un message clair et en français

## Git

- Les messages de commits doivent être en français.
- Les messages de commits doivent commencer par le picto 🤖 et garder un message concernant les modifications seulement.

## Code Style Guidelines

- Les entités & DTO divers doivent avoir des propriétés public par défaut.
- Respecter les conventions PSR & Symfony.
- Utiliser des types PHP 8 pour les paramètres et les valeurs de retour.
- Les contrôleurs doivent être fins, la logique métier doit être déplacée dans des services.

## Inscructions diverses

- Ne JAMAIS editer le fichier `phpstan.neon`.

