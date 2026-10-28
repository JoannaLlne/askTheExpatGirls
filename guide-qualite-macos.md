# Guide qualité du code sur macOS

Ce guide t'explique comment installer sur ton Mac les outils de qualité du projet, puis comment t'en servir au quotidien.
Le README décrit l'installation pour Linux et WSL : ici, tout est adapté à macOS.

Il y a deux familles d'outils :
- les **contrôles composer** (validation de composer.json, lint Symfony, Rector, style, PHPStan, tests) : ils tournent directement sur ton Mac, rien à installer à part `composer install` ;
- **SonarQube** : un serveur d'analyse avec un tableau de bord web. Il tourne dans Docker.

Le fichier `findings.md` liste de vrais problèmes trouvés dans le projet. Tu peux les corriger comme exercices.

## Récupérer les outils

Après un `git pull`, installe les dépendances. Cela ajoute phpcs, PHPStan et Rector.
```bash
composer install
```

Vérifie que les contrôles sont connus. Les scripts `composer-validate`, `lint`, `rector-dry-run`, `code-style`, `static-analysis`, `quality`... apparaissent avec leur description.
```bash
composer list
```

## Installer pcov (couverture de code)

La couverture de code, c'est le pourcentage de lignes exécutées par les tests. L'extension PHP `pcov` sait la mesurer.

Commence par savoir quel PHP tu utilises.
```bash
which php
```

- Si ça affiche `/opt/homebrew/bin/php` (Mac à puce Apple) ou `/usr/local/bin/php` (Mac Intel), c'est le PHP de Homebrew. Installe pcov avec pecl :
```bash
pecl install pcov
```

pecl compile l'extension. S'il se plaint d'outils manquants, installe d'abord les outils de développement d'Apple, puis relance la commande précédente.
```bash
xcode-select --install
```

Vérifie que c'est bon : la commande doit afficher `pcov`.
```bash
php -m | grep pcov
```

- Si `which php` affiche autre chose (MAMP, Herd...), l'installation est différente. Demande de l'aide plutôt que de deviner.

## Utiliser les contrôles au quotidien

Avant chaque commit, lance tous les contrôles d'un coup. La commande s'arrête au premier contrôle en échec.
```bash
composer quality
```

Elle enchaîne, dans cet ordre : la validation de composer.json, les vérifications de Symfony (lint), Rector (en simulation), le style, PHPStan, puis les tests. Le détail de chaque script est dans le [README, section Qualité du code](README.md#qualité-du-code).

**Aujourd'hui, elle échoue, et c'est normal.** Les règles ont été ajoutées après l'écriture du code. Il reste donc des problèmes (chiffres approximatifs, mesurés le 28/10/2026) :
- un template Twig cassé, `templates/legal/terms.html.twig` : c'est le premier obstacle que tu rencontreras, car `composer lint` passe avant tous les autres contrôles (voir `findings.md`, n° 6) ;
- Rector propose des modifications dans environ 65 fichiers ;
- environ 290 erreurs de style, que `composer code-style-fix` corrige automatiquement, plus 14 lignes trop longues à corriger à la main ;
- environ 110 erreurs PHPStan (niveau 8), à corriger à la main ;
- un test qui échoue (c'est l'un des problèmes de `findings.md`).

Comme `composer quality` s'arrête au premier échec, tu ne vois pas les contrôles suivants tant que celui-ci échoue. Pour voir tout de suite ce qui t'attend, lance les scripts un par un : `composer lint`, `composer rector-dry-run`, `composer code-style`, `composer static-analysis` et `composer unit`.

Je te conseille de suivre l'ordre de `composer quality`. D'abord le template Twig cassé. Cette commande affiche le fichier et la ligne en cause :
```bash
composer lint
```

Corrige-le à la main (`findings.md`, n° 6), puis relance `composer lint` jusqu'à ce qu'il passe. La validation de composer.json (`composer composer-validate`) passe déjà.

Ensuite Rector. Regarde d'abord ce qu'il propose, sans rien modifier :
```bash
composer rector-dry-run
```

Si les modernisations te conviennent, applique-les :
```bash
composer rector-fix
```

Puis la correction automatique du style :
```bash
composer code-style-fix
```

Regarde les changements avec `git diff` avant de les committer. Rector et cette correction ajoutent `declare(strict_types=1)` en haut des fichiers : PHP devient plus strict sur les types, donc relance les tests (`php bin/phpunit`) pour vérifier que rien ne casse.

Ensuite, corrige à la main ce qui reste, en relançant `composer quality` après chaque étape :
- les quelques lignes trop longues (plus de 120 caractères) signalées par `composer code-style` ;
- les erreurs PHPStan, une par une. Chaque erreur a un identifiant (par exemple `argument.type`) : cherche-le sur Internet pour comprendre le problème. Deux d'entre elles sont un vrai bug (`findings.md`, n° 7) ;
- le test qui échoue (`findings.md`, n° 2).

## Docker en 5 minutes

**Docker** fait tourner des programmes dans des **conteneurs** : des boîtes isolées, sans rien installer sur ton Mac. Un conteneur est créé à partir d'une **image**, un programme déjà préparé et téléchargeable. Un **volume** est l'endroit où un conteneur garde ses données quand on l'arrête puis qu'on le relance.

### Installer Docker Desktop

1. Regarde ton processeur : menu Apple > À propos de ce Mac. « Puce Apple » veut dire Apple Silicon, « Processeur Intel » veut dire Intel.
2. Télécharge la version correspondante sur https://www.docker.com/products/docker-desktop/ (gratuit pour un usage personnel et l'apprentissage).
3. Ouvre le fichier `.dmg`, glisse Docker dans Applications, lance-le et accepte les conditions.
4. Dans les réglages (Settings > Resources), donne au moins 4 Go de mémoire (Memory) : SonarQube en a besoin. Applique le changement.

Docker Desktop doit être **lancé** (icône de baleine dans la barre de menus) pour que toute commande `docker` fonctionne.

Vérifie l'installation : la commande doit afficher un message « Hello from Docker! ».
```bash
docker run --rm hello-world
```

### Les 4 commandes dont tu as besoin

Lance-les depuis la racine du projet. Démarrer SonarQube, en arrière-plan :
```bash
docker compose -f compose.sonar.yaml up -d
```

Voir ce qui tourne :
```bash
docker compose -f compose.sonar.yaml ps
```

Suivre les journaux (les logs) de SonarQube. Ctrl+C pour quitter :
```bash
docker compose -f compose.sonar.yaml logs -f sonarqube
```

Arrêter SonarQube. Tes analyses sont conservées :
```bash
docker compose -f compose.sonar.yaml down
```

**Attention : n'ajoute jamais `-v` à `down`.** Cette option supprime les volumes, donc toutes les analyses ET le compte administrateur de SonarQube.

## SonarQube : première installation

SonarQube est un serveur qui analyse le code. Son tableau de bord web montre les bugs, les failles de sécurité, la duplication et la couverture de code. Il importe aussi le rapport de PHPStan.

### Démarrer

Lance `up -d` (voir ci-dessus). Le premier démarrage télécharge les images et prend quelques minutes. Suis les logs jusqu'à voir `SonarQube is operational`, puis ouvre http://localhost:9000.

### Se connecter

Connecte-toi avec `admin` / `admin`. SonarQube te demande tout de suite un nouveau mot de passe : garde-le en lieu sûr. Ce compte n'existe que dans ton SonarQube local.

### Créer le projet

1. Clique sur Create Project, puis Local project.
2. Display name : `Ask The Expat Girls`.
3. SonarQube propose la clé `Ask-The-Expat-Girls`. Garde-la exactement : elle doit être identique à `sonar.projectKey` dans `sonar-project.properties`.
4. Main branch : `main`. Clique sur Next.
5. Pour le « New code », choisis « Follows the instance's default », puis Create project.

Le **« new code »** est le code modifié après la première analyse. Le quality gate (la barrière de qualité, voir plus bas) ne juge que lui.

### Créer le token

Un **token** est un mot de passe réservé aux programmes. Le scanner l'utilise pour envoyer ses analyses à SonarQube.

1. Choisis la méthode d'analyse « Locally », puis « Generate a project token ».
2. Donne-lui un nom (par exemple `local`) et une date d'expiration, puis clique sur Generate.
3. Copie le token tout de suite : il commence par `sqp_` et n'est affiché qu'une seule fois.
4. Ignore les écrans suivants : le scanner est déjà configuré dans `compose.sonar.yaml`.

### Enregistrer le token dans ton terminal

macOS utilise zsh : le fichier à modifier est `~/.zshrc` (et non `~/.bashrc`, comme dans le README). Remplace `ton_token` par ton vrai token.
```bash
echo 'export SONAR_TOKEN=ton_token' >> ~/.zshrc
```

Recharge le fichier :
```bash
source ~/.zshrc
```

Ne mets jamais le token dans un fichier versionné, ni dans `.env` (Docker Compose lit `.env` automatiquement).

## Lancer une analyse

À chaque fois, depuis la racine du projet, avec Docker Desktop lancé et SonarQube démarré.

1. Crée le dossier des rapports :
   ```bash
   mkdir -p var/reports
   ```

2. Prépare le cache de Symfony. PHPStan en a besoin : il lit le conteneur de services que cette commande génère :
   ```bash
   php bin/console cache:warmup --env=dev
   ```

3. Génère le rapport PHPStan. N'utilise pas `composer static-analysis` ici : son affichage s'ajouterait au fichier et le rendrait illisible pour SonarQube.
   ```bash
   vendor/bin/phpstan analyse --memory-limit=1G --no-progress --error-format=json > var/reports/phpstan.json
   ```

4. Lance les tests avec la couverture. Il faut la base de test du README (section « Partie tests ») et pcov. Un test en échec n'empêche pas les rapports d'être créés.
   ```bash
   php bin/phpunit --coverage-clover var/reports/coverage.xml --log-junit var/reports/junit.xml
   ```

5. Lance le scanner. Il se termine par `EXECUTION SUCCESS` quand tout s'est bien passé.
   ```bash
   docker compose -f compose.sonar.yaml run --rm sonar-scanner
   ```

Rafraîchis ensuite http://localhost:9000.

## Lire les résultats

Les noms des menus peuvent varier un peu selon la version de SonarQube.

- **Vue d'ensemble du projet** : deux onglets, « New Code » et « Overall Code ». Après la première analyse, New Code est vide : regarde Overall Code.
- **Quality gate** : « Passed » ou « Failed ». Il ne vérifie que le nouveau code (assez de couverture sur les lignes modifiées, aucun nouveau problème). Il passe à la première analyse, car il n'y a pas encore de nouveau code. S'il échoue plus tard, le scanner se termine en erreur : c'est voulu.
- **Issues** : la liste des problèmes, filtrable à gauche (par qualité logicielle : sécurité, fiabilité, maintenabilité ; par sévérité ; par règle). Ceux qui viennent de PHPStan sont marqués « external ». Clique sur un problème pour voir le code et l'explication « Why is this an issue? » : c'est une bonne façon d'apprendre.
- **Faux positifs** : SonarQube se trompe parfois. Exemple : il signale comme vulnérabilité la protection CSRF désactivée dans `src/Form/ServiceFilterType.php`. Or c'est un formulaire de filtre envoyé en GET, qui ne modifie rien : c'est sans danger. Après avoir vérifié, change le statut du problème (« Accept » ou « False positive ») et ajoute un commentaire qui explique pourquoi.
- **Security Hotspots** : du code à **relire**, pas forcément un bug. Après vérification, tu peux le marquer comme sûr.
- **Couverture** : le pourcentage de lignes de code exécutées par les tests, visible fichier par fichier.

Chiffres de la première analyse (28/10/2026, avant le passage de PHPStan au niveau 8) : 120 problèmes (111 de PHPStan, 9 des règles propres à SonarQube) et environ 21 % de couverture. Avec la configuration actuelle (`phpstan.dist.neon`), PHPStan en remonte environ 110.

## Dans l'éditeur (optionnel)

L'extension « SonarQube for IDE » affiche les règles de SonarQube pendant que tu tapes, sans serveur.
- VS Code ou Cursor : extension `SonarSource.sonarlint-vscode`.
- PhpStorm : Settings > Plugins > Marketplace, cherche « SonarQube for IDE ».

## En cas de problème

- **« Cannot connect to the Docker daemon »** : Docker Desktop n'est pas lancé.
- **« port is already allocated » ou « address already in use » (port 9000)** : un autre programme utilise ce port. Avec le PHP de Homebrew, c'est souvent php-fpm : `brew services list` le montre, et `brew services stop php` l'arrête. Autre solution : dans `compose.sonar.yaml`, remplace `"127.0.0.1:9000:9000"` par `"127.0.0.1:9001:9000"` et utilise http://localhost:9001.
- **SonarQube ne devient jamais « operational » ou redémarre en boucle** : regarde les logs. Le plus souvent, il manque de mémoire : augmente celle de Docker Desktop (voir « Docker en 5 minutes »).
- **« You're not authorized to analyze this project or the project doesn't exist »** : le token manque dans ce terminal (`echo $SONAR_TOKEN` n'affiche rien : ouvre un nouveau terminal ou lance `source ~/.zshrc`), ou la clé du projet dans SonarQube n'est pas exactement `Ask-The-Expat-Girls`.
- **Couverture à 0 % dans SonarQube** : pcov n'est pas installé (voir « Installer pcov »), ou l'étape phpunit a été sautée.
- **`could not find driver` en lançant les tests** : l'extension PHP pour MySQL manque. Demande de l'aide.

## Récap

| Quand | Commande |
| --- | --- |
| Avant chaque commit | `composer quality` |
| Corriger le style automatiquement | `composer code-style-fix` |
| De temps en temps, pour la vue d'ensemble | les 5 étapes de « Lancer une analyse » |
| Quand tu as fini | `docker compose -f compose.sonar.yaml down` |
