# Findings

Problèmes relevés le 2026-10-28 lors de la mise en place locale du projet et du travail sur les outils de qualité. Aucun n'est encore corrigé.

### 1. Images par défaut cassées (services et packages)

- **Statut** : Non corrigé
- **Symptôme** : sur la page `/service`, les 5 cartes de services affichent une image cassée (seul le texte alternatif est visible), alors que le logo et les 4 photos du haut de la même page s'affichent correctement. Le même défaut existe pour les packages et dans la liste de l'administration.
- **Cause** : le getter de l'entité ne renvoie jamais `null`, il renvoie un nom de fichier par défaut.

  ```php
  // src/Entity/Service.php:206
  return $this->image ?? 'default-service.jpg';
  ```

  Le template décide de l'image à partir de ce getter (`templates/service/services.html.twig:106-113`) :

  ```twig
  {% if service.image %}
      <img src="{{ asset('uploads/services/' ~ service.image) }}" ...>
  {% else %}
      <img src="{{ asset('images/default-service.jpg') }}" ...>
  {% endif %}
  ```

  Comme `service.image` vaut toujours au moins `'default-service.jpg'`, la branche `else` n'est jamais atteinte. La page demande donc `/uploads/services/default-service.jpg`, qui n'existe pas (le dossier `public/uploads/services/` n'existe même pas en local). L'image par défaut est dans `assets/images/default-service.jpg` (AssetMapper) : c'est celle que la branche `else` afficherait.

  Même défaut pour les packages : `src/Entity/ExpatPackages.php:90` (`?? 'default-packages.jpg'`) avec `templates/expat_packages/packages.html.twig:58-62`.

  Impact sur l'administration : `src/Controller/Admin/ServiceCrudController.php:52` et `src/Controller/Admin/ExpatPackagesCrudController.php:32` utilisent `ImageField::new('image', 'Photo')` avec `setBasePath('/uploads/...')`. Un service ou un package sans image affiche une miniature cassée dans la liste, et le formulaire d'édition considère le fichier `default-*.jpg`, inexistant, comme le fichier actuellement envoyé.

  Remarque : `public/uploads/` est ignoré par Git (`.gitignore:12-14`). Sur un clone neuf, même un service avec une vraie image envoyée affiche donc une image cassée (le fichier n'existe que sur la machine où il a été envoyé). C'est normal en développement, ce n'est pas à corriger.
- **Correction proposée** : dans les deux entités, renvoyer la valeur telle quelle (nullable). C'est le template, et non l'entité, qui est responsable de l'image par défaut.

  ```php
  public function getImage(): ?string
  {
      return $this->image;
  }
  ```

  Vérifier ensuite en base qu'aucune ligne n'a `default-service.jpg` ou `default-packages.jpg` dans la colonne `image` (une sauvegarde depuis l'administration a pu l'y écrire).

### 2. Annulation d'un rendez-vous sans contrôle du propriétaire

- **Statut** : Non corrigé
- **Symptôme** : la route d'annulation supprime un rendez-vous sans vérifier qu'il appartient à l'utilisateur connecté. Ce n'est pas exploitable aujourd'hui, mais la protection est accidentelle. Le test `testCannotCancelAnotherUsersAppointment` échoue (404 au lieu de 403).
- **Cause** : dans `src/Controller/AppointmentController.php`, la classe a le préfixe `/application/{id}/appointment` (ligne 17) et l'action `cancel()` la route `/cancel` (lignes 82-100). Dans cette URL, le `{id}` est l'identifiant du **rendez-vous**, pas celui du dossier (`/application/{id}/appointment/cancel`). Le contrôle d'accès est en commentaire (ligne 83) : il référence un argument `application` que `cancel()` n'a pas, ce qui l'a probablement fait planter.

  ```php
  //#[IsGranted('VIEW', subject: 'application')]
  public function cancel(Appointment $appointment, Request $request, EntityManagerInterface $em): Response
  {
      if ($this->isCsrfTokenValid('cancel-' . $appointment->getId(), $request->request->get('_token'))) {
          $em->remove($appointment);
  ```

  Le rendez-vous est chargé depuis l'URL puis supprimé si le jeton CSRF `cancel-{id}` est valide, sans lien avec l'utilisateur connecté.

  Pourquoi ce n'est pas exploitable : les jetons `cancel-{id}` sont stockés en session (`config/packages/csrf.yaml` ne déclare comme stateless que `submit`, `authenticate` et `logout`). Le seul endroit qui génère ces jetons est le profil (`templates/user/profile.html.twig:154-155`), que `src/Controller/UserController.php:23` réserve à son propriétaire. La protection est un effet de bord du CSRF, pas un contrôle d'autorisation : elle disparaît si une autre page affiche ces formulaires, si le jeton devient stateless, ou si le CSRF est retiré.

  Test : `tests/Controller/AppointmentControllerTest.php:72` poste vers `/appointment/{id}/cancel`, une URL qui n'existe pas, d'où le 404. Le commentaire de la ligne 73 est aussi inexact : avec un jeton invalide, le contrôleur redirige avec un message d'erreur, il ne renvoie pas 403. Le 403 doit donc venir d'un contrôle fait avant le CSRF. Suite actuelle : 10 tests, 9 réussis, celui-ci est le seul en échec.
- **Correction proposée** : au début de `cancel()`, réutiliser le voter existant (`src/Security/Voter/ApplicationVoter.php`) et supprimer la ligne commentée.

  ```php
  $this->denyAccessUnlessGranted('VIEW', $appointment->getApplication());
  ```

  Corriger l'URL du test (`'/application/' . $appointment->getId() . '/appointment/cancel'`) : avec les deux changements, il doit obtenir son 403.

### 3. Les fixtures exigent des services déjà en base

- **Statut** : Non corrigé
- **Symptôme** : sur une base neuve, `php bin/console doctrine:fixtures:load` vide la base puis ne crée rien. Seul le message « Aucun service en base : crée au moins un service avant de lancer ces fixtures. » apparaît.
- **Cause** : `src/DataFixtures/AppFixtures.php:30-34` s'arrête si aucun service n'existe, et aucune migration n'en insère.

  ```php
  $services = $manager->getRepository(Service::class)->findAll();
  if (empty($services)) {
      echo "Aucun service en base : ...\n";
      return;
  }
  ```

  Sans `--append`, la commande purge d'abord toutes les tables, services compris : la liste est donc toujours vide. Seul `--append` fonctionne, avec des services créés à la main. Les fixtures ne créent ni package ni compte admin (pour l'admin : `php bin/console app:create-admin`).

  Contournement utilisé le 2026-10-28 sur la base de développement `app` : 5 services insérés à la main avec « (dev sample) » dans leur description, puis `doctrine:fixtures:load --append`.
- **Correction proposée** : faire créer par les fixtures leurs propres services (et packages) avant le reste, et supprimer cette vérification, pour que `doctrine:fixtures:load` fonctionne sur une base vide.

### 4. Incohérences de configuration et de documentation

- **Statut** : Non corrigé
- **Symptôme** : plusieurs fichiers de configuration et le README se contredisent, ce qui fait échouer ou dérouter une installation sur une machine neuve.
- **Cause** :
  - Base de données : `compose.yaml:5` déclare `postgres` 16 (valeur par défaut de la recette Symfony Flex), alors que l'application utilise MySQL 8 : les migrations contiennent du SQL propre à MySQL (`ENGINE = InnoDB`, `utf8mb4`, `TINYINT`, par exemple `migrations/Version20261009130737.php:54`), et `.env.test` et le README annoncent MySQL. Même reste dans `config/packages/doctrine.yaml:13-14` (`PostgreSQLPlatform`), sans effet sur MySQL.
  - README : « Pré-requis » indique PHP >= 8.2 (`README.md:16`) alors que `composer.json:7` exige PHP >= 8.5. Il ne mentionne pas non plus l'extension PHP `pdo_mysql`.
  - `.env` : retiré de Git par le commit `80364e6`, et ignoré par `.gitignore:3`, alors que ses trois versions committées ne contenaient que des valeurs factices (`APP_SECRET=` vide, mot de passe `!ChangeMe!`, `MAILER_DSN=null://null`). Sur un clone neuf, `bin/console` s'arrête avec `Unable to read the ".env" environment file`, et `composer install` échoue donc à son étape `cache:clear` (`composer.json:88-94`). Le README (ligne 29) demande pourtant de configurer ce fichier.
  - Base de test : `.env.test:6` pointe vers `app_test`, et `config/packages/doctrine.yaml:28` ajoute le suffixe `_test` en environnement de test : la base réellement utilisée est `app_test_test`. Sur cette machine, `.env.test.local` (ignoré par Git) pointe vers `app`, ce qui donne `app_test` et masque le problème.
- **Correction proposée** :
  - Aligner `compose.yaml` sur MySQL 8 (ou retirer le service si Docker n'est pas utilisé pour la base), et supprimer `identity_generation_preferences` de `doctrine.yaml`.
  - Dans le README : PHP >= 8.5, et ajouter `pdo_mysql` (`sudo apt install php8.5-mysql`).
  - Suivre la convention Symfony : committer `.env` avec des valeurs factices (`APP_SECRET` vide, déjà défini dans `.env.dev`, et `APPOINTMENTS_ENABLED=false`, requis par `config/services.yaml:12`), retirer `/.env` de `.gitignore`, et mettre les vraies valeurs dans `.env.local` (ignoré par Git).
  - Faire pointer `.env.test` vers `app` : avec le suffixe, la base de test sera `app_test` (convention Symfony).

### 5. Redirection vers une route inexistante dans la prise de rendez-vous

- **Statut** : Non corrigé
- **Symptôme** : si le créneau choisi vient d'être pris par quelqu'un d'autre au moment de la validation, l'utilisateur obtient une erreur 500 au lieu du message « Sorry, this slot has just been booked ».
- **Cause** : `src/Controller/AppointmentController.php:58` redirige vers la route `account_appointment_book`, qui n'existe pas (`php bin/console debug:router` ne la liste pas). La route de prise de rendez-vous s'appelle `appointment_book`.

  ```php
  return $this->redirectToRoute('account_appointment_book', ['id' => $application->getId()]);
  ```
- **Correction proposée** : remplacer `account_appointment_book` par `appointment_book`. Ajouter un test fonctionnel qui réserve un créneau déjà pris, pour couvrir ce cas.

### 6. Template Twig cassé : la page des conditions générales

- **Statut** : Non corrigé
- **Symptôme** : `php bin/console lint:twig templates/legal/terms.html.twig` échoue (ligne 133) avec `Unexpected token "end of template" ("end of statement block" expected)`. La page `/terms` (route `app_terms`, lien « Terms & Conditions » du pied de page) plante donc au rendu (`Twig\Error\SyntaxError`, erreur 500). `composer lint`, et donc `composer quality`, s'arrêtent sur ce fichier : c'est le premier obstacle que rencontre le contrôle de qualité.
- **Cause** : le bloc `body`, ouvert à la ligne 5, n'est jamais refermé. Le fichier s'arrête après le commentaire d'en-tête de la section 7, sans contenu ni `{% endblock %}`.

  ```twig
  {# templates/legal/terms.html.twig:5 #}
  {% block body %}
  ```

  ```twig
  {# templates/legal/terms.html.twig:129-131 : fin du fichier, juste après la section 6 #}
  {# ============================================================
         7. NO PROFESSIONAL ADVICE
         ============================== #}
  ```

  Les trois autres pages légales (`cookie-policy`, `privacy-policy`, `legal-notice`) ferment bien leur bloc avec `{% endblock %}`. Côté HTML, les balises `<section>` sont équilibrées (7 ouvertes, 7 fermées) : seul le bloc Twig manque, et la section 7 semble n'avoir jamais été écrite.
- **Correction proposée** : écrire (ou retirer) la section 7, puis fermer le bloc à la fin du fichier avec `{% endblock %}`. Vérifier avec `php bin/console lint:twig templates`.

### 7. `QuestionnaireRepository::findById()` et `findByEmail()` : type de retour faux

- **Statut** : Non corrigé
- **Symptôme** : les deux méthodes déclarent renvoyer `?Questionnaire`, mais renvoient un tableau. Dès qu'elles sont appelées, PHP lève une `TypeError`, que la requête trouve un résultat ou non (un tableau, même vide, n'est jamais un `?Questionnaire`). PHPStan le signale une fois le chargeur Doctrine activé (`tests/object-manager.php`, `phpstan.dist.neon`) : `Method App\Repository\QuestionnaireRepository::findById() should return App\Entity\Questionnaire|null but returns list<App\Entity\Questionnaire>.` (identifiant `return.type`, lignes 23 et 30).
- **Cause** : `src/Repository/QuestionnaireRepository.php:19-31`. La requête est correcte, mais elle se termine par `getResult()`, qui renvoie toujours un tableau de résultats.

  ```php
  public function findById(int $id): ?Questionnaire {
      $qb = $this->createQueryBuilder('q');
      $qb->where('q.id = :id')
          ->setParameter('id', $id);
      return $qb->getQuery()->getResult();
  }
  ```

  Ces méthodes ne sont appelées nulle part : `grep -rnE "findById|findByEmail" src/ templates/ tests/` ne trouve que leurs définitions. Le défaut est donc dormant, mais le premier appel le déclenchera.
- **Correction proposée** : terminer la requête par `->getOneOrNullResult()`. Pour `findByEmail()`, attention : la colonne `email` de `Questionnaire` n'est pas unique, et `getOneOrNullResult()` lève une `NonUniqueResultException` si plusieurs questionnaires ont la même adresse. Le plus simple est de supprimer ces deux méthodes et d'utiliser celles de Doctrine, qui ont le bon type de retour : `find($id)` et `findOneBy(['email' => $email])`.
