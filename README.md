[![Codacy Badge](https://app.codacy.com/project/badge/Grade/2a905ce9ddda4025981aa5c5abfbf78b)](https://app.codacy.com/gh/njarach/todo-list/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)

# ToDoList

Application Todo-list développée avec Symfony 7.

## Prérequis

- PHP 8.2.0 minimum
- Composer

## Installation

1. **Cloner le projet**
   ```bash
   git clone https://github.com/njarach/todo-list.git
   cd todo-list
   ```

2. **Configuration**
   ```bash
   cp .env .env.local
   ```
   Modifier les variables d'environnement dans `.env.local` avec vos informations de base de données.

3. **Installer les dépendances**
   ```bash
   composer install
   ```

4. **Base de données**
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

## Développement

Le projet utilise AssetMapper (pas besoin de Node.js).

## Production

1. **Compiler les assets**
   ```bash
   php bin/console asset-map:compile
   ```

2. **Optimiser l'autoloader**
   ```bash
   composer dump-autoload -o
   composer dump-env prod
   ```

## Tests

```bash
php bin/phpunit
php bin/phpunit --coverage-html coverage
```