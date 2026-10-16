# Nom du projet
Ask the Expat Girls

## Description
Site Internet proposant des services d'aides administratives pour les personnes anglophones
souhaitant s'expatrier en France.

## Pré-requis
PHP >= 8.2
Composer
Serveur Apache ou Nginx
MySQL >= 8.0
Symfony CLI (recommandé, pour symfony serve)

## Installation
### Clôner le dépot :
https://github.com/JoannaLlne/askTheExpatGirls.git

### Installer les dépendances : 
Avec la commande : _composer install_ 

### Configurer l'environnement avec les fichiers .env et .env.local :
- DATABASE_URL : identifiants de connexion à la base MySQL
- MAILER_DSN : identifiants Brevo

### Installer le certificat local HTTPS
Avec la commande : _symfony server:ca:install_

### Créer la base de données et effectuer les migrations
Avec les commandes
- symfony console doctrine:database:create 
- symfony console doctrine:migrations:migrate

### Démarrer le serveur
Avec la commande : _symfony serve_

## Partie tests
### Créer la base de données test
Avec les commandes :
- symfony console doctrine:database:create --env=test
- symfony console doctrine:migrations:migrate --env=test

### Lancer les tests
Tous les tests : _php bin/phpunit_
Un test précis : _php bin/phpunit tests/NomTest.php_

#### Le projet comprend trois niveaux de test
- Utilitaires : logique isolée, sans base de données
  --> tests/Service/, tests/Security/

- Intégration : vérifie les requêtes Doctrine avec une vraie base
  --> tests/Repository/

- Fonctionnels : simule une vraie requête HTTP, pour vérifier les contrôles d'accès par exemple
--> tests/Controller/

Base de données test automatiquement remise dans son état initial après chaque test grâce à doctrine-test-bundle

## Fonctionnalités
La prise de rendez-vous peut être désactivée via la variable d'environnement :
APPOINTMENTS_ENABLED=false


## Auteur
Joanna Lalanne — projet réalisé dans le cadre d'un stage durant une formation de développeur web et web mobile.
