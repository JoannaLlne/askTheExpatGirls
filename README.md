# Nom du projet
Ask the Expat Girls

## Description
Site Internet proposant des services d'aides administratives pour les personnes anglophones
souhaitant s'expatrier en France.

Les visiteurs peuvent remplir un questionnaire pour décrire leur situation et choisir un service ou 
un package. Cette soumission crée automatiquement un dossier de suivi, et l'utilisateur peut s'inscrire pour
y accéder dans un espace personnel, suivre leur avancement, et également prendre rendez-vous.

Un back-office permet aux admins de gérer les services et les packages qu'ils proposent, mais aussi de voir 
les utilisateurs, les dossiers et les rendez-vous.

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

## Qualité du code
Plusieurs outils lisent le code pour repérer les erreurs de style, les bugs possibles et le code à moderniser.

Sur macOS, pour tout installer (pcov, Docker, SonarQube) et apprendre à s'en servir : voir [guide-qualite-macos.md](guide-qualite-macos.md).

### Pré-requis
- Couverture de code : l'extension PHP pcov est nécessaire.
  Avec la commande : _sudo apt install php8.5-pcov_
  Pour vérifier qu'elle est installée : _php -m | grep pcov_
- Serveur SonarQube : Docker est nécessaire. Sous Windows, activer l'intégration WSL dans Docker Desktop
  (Settings > Resources > WSL integration).

### Les outils de qualité
Le style, l'analyse statique et Rector analysent les dossiers src/ et tests/. composer-validate et lint vérifient
composer.json, la configuration, les templates et le mapping Doctrine. La commande _composer list_ affiche leur description.

- _composer composer-validate_ : vérifie que composer.json est valide et strict (par exemple aucune contrainte de version
  sans borne). Ne modifie aucun fichier.
- _composer lint_ : lance les vérifications de Symfony lui-même : conteneur de services, fichiers YAML de config/,
  templates Twig (erreurs de syntaxe) et mapping Doctrine (sans se connecter à la base). Ne modifie aucun fichier.
- _composer rector-dry-run_ : montre les modernisations que Rector appliquerait (rector.php) : syntaxe de la version PHP
  de composer.json, code mort, types déclarés, bonnes pratiques Symfony, Doctrine, PHPUnit et Twig. Ne modifie aucun fichier.
- _composer rector-fix_ : applique ces modernisations. À relire avec _git diff_, puis lancer _composer code-style-fix_
  (Rector ne suit pas toujours le style).
- _composer code-style_ : vérifie le style du code avec PHP_CodeSniffer. Les règles sont dans phpcs.xml.dist :
  PSR-12, règles pour la syntaxe PHP 8 (attributs, enums, fonctions fléchées, arguments nommés, types nullable et DNF),
  declare(strict_types=1) obligatoire, fonctions de debug interdites (dump, dd, var_dump, print_r),
  use triés, utilisés et jamais groupés, virgules finales, lignes de 120 caractères maximum. Ne modifie aucun fichier.
- _composer code-style-fix_ : corrige automatiquement ce qui peut l'être (phpcbf). Le reste est à corriger à la main.
- _composer static-analysis_ : réchauffe d'abord le cache Symfony (_php bin/console cache:warmup --env=dev_), puis lance
  PHPStan niveau 8 (phpstan.dist.neon). Trouve des bugs sans exécuter le code : mauvais type, méthode ou propriété
  inexistante, valeur possiblement null, mapping Doctrine incohérent. Ne modifie aucun fichier.

#### Ordre conseillé avant un commit
1. _composer composer-validate_ puis _composer lint_
2. _composer rector-dry-run_ puis, si le résultat convient, _composer rector-fix_
3. _composer code-style-fix_ puis _composer code-style_
4. _composer static-analysis_
5. _composer unit_ (équivalent de _php bin/phpunit_)

Pour tout lancer d'un coup, dans cet ordre (validation de composer.json, lint, rector, style, phpstan, tests) :
_composer quality_. La commande s'arrête au premier contrôle en échec.

### Couverture de code
Nécessite pcov (voir Pré-requis).
Rapport HTML avec la commande : _php bin/phpunit --coverage-html var/coverage_
Puis ouvrir var/coverage/index.html dans un navigateur. Le dossier var/ est ignoré par Git.

### SonarQube
#### Dans l'éditeur
Installer l'extension "SonarQube for IDE" (id : SonarSource.sonarlint-vscode) pour Cursor ou VS Code.
Elle analyse les fichiers pendant la saisie, sans serveur. Pour PhpStorm : Settings > Plugins > Marketplace,
chercher "SonarQube for IDE". Si le projet est ouvert via WSL, installer l'extension côté WSL.

#### Serveur local
Il donne un tableau de bord, le quality gate, la duplication, et importe les rapports PHPStan et de couverture.
Il est défini dans compose.sonar.yaml, séparé de compose.yaml. Les réglages d'analyse sont dans sonar-project.properties.
Toutes les commandes se lancent depuis la racine du projet.

1. Démarrer : _docker compose -f compose.sonar.yaml up -d_
   Le premier démarrage prend 1 à 2 minutes : attendre que http://localhost:9000 réponde.
2. Première fois seulement : se connecter avec admin / admin (Sonar demande de changer le mot de passe),
   puis "Create a local project" avec la clé exacte Ask-The-Expat-Girls (la même que dans sonar-project.properties),
   branche principale main, méthode d'analyse "Locally", puis "Generate a token".
   Copier le token tout de suite (il commence par sqp_ et n'est affiché qu'une fois).
   Plus tard : avatar > My Account > Security, type "Project Analysis Token".
3. Enregistrer le token dans le shell, jamais dans un fichier versionné ni dans .env (Docker Compose lit .env tout seul) :
   ```bash
   echo 'export SONAR_TOKEN=ton_token' >> ~/.bashrc && source ~/.bashrc
   ```
4. Générer les rapports importés par Sonar :
   ```bash
   mkdir -p var/reports
   php bin/console cache:warmup --env=dev
   vendor/bin/phpstan analyse --memory-limit=1G --no-progress --error-format=json > var/reports/phpstan.json
   php bin/phpunit --coverage-clover var/reports/coverage.xml --log-junit var/reports/junit.xml
   ```
   Le _cache:warmup_ est nécessaire : PHPStan lit le conteneur Symfony généré. On n'utilise pas ici _composer static-analysis_,
   car sa sortie corromprait le fichier JSON.
5. Lancer l'analyse : _docker compose -f compose.sonar.yaml run --rm sonar-scanner_
   Puis consulter les résultats sur http://localhost:9000
6. Arrêter : _docker compose -f compose.sonar.yaml down_ (les analyses sont gardées dans les volumes Docker).

Limite : SonarQube Community n'analyse que la branche main (pas d'analyse par branche ni par pull request).

#Commits
  C'est bien de suivre une convention dans l'écriture des commit messages, au taff on utilise https://www.conventionalcommits.org/en/v1.0.0/

## Fonctionnalités
La prise de rendez-vous peut être désactivée via la variable d'environnement :
APPOINTMENTS_ENABLED=false


## Auteur
Joanna Lalanne — projet réalisé dans le cadre d'un stage durant une formation de développeur web et web mobile.
