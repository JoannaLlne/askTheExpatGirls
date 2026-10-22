<?php

namespace App\DataFixtures;

use App\Entity\Application;
use App\Entity\Appointment;
use App\Entity\Questionnaire;
use App\Entity\Service;
use App\Entity\User;
use App\Enum\ApplicationStatus;
use App\Enum\AppointmentStatus;
use App\Enum\ServiceCategory;
use App\Enum\Situation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Réutilisation des services déjà en base
        $services = $manager->getRepository(Service::class)->findAll();
        if (empty($services)) {
            echo "Aucun service en base : crée au moins un service avant de lancer ces fixtures.\n";
            return;
        }

        // Création d'utilisateurs
        $users = [];
        for ($i = 0; $i < 15; $i++) {
            $user = new User();
            $user->setFirstName($faker->firstName());
            $user->setLastName($faker->lastName());
            $user->setEmail($faker->unique()->safeEmail());
            $user->setPassword($this->hasher->hashPassword($user, 'password123'));
            $user->setCreatedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months', 'now')));
            $user->setIsVerified(true);
            $manager->persist($user);
            $users[] = $user;
        }

        // Création de questionnaires
        for ($i = 0; $i < 30; $i++) {
            $questionnaire = new Questionnaire();
            $questionnaire->setFirstName($faker->firstName());
            $questionnaire->setLastName($faker->lastName());
            $questionnaire->setEmail($faker->unique()->safeEmail());
            $questionnaire->setNationality($faker->country());
            $questionnaire->setSituation($faker->randomElement(Situation::cases()));
            $questionnaire->setHelpReason($faker->randomElement(ServiceCategory::cases()));
            $questionnaire->setAdditionalInfo($faker->optional(0.6)->sentence());

            $submittedAt = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-3 months', 'now'));
            $questionnaire->setSubmittedAt($submittedAt);
            $manager->persist($questionnaire);

            // Création des dossiers
            $application = new Application();
            $application->setEmail($questionnaire->getEmail());
            $application->setQuestionnaire($questionnaire);
            $application->setService($faker->randomElement($services));
            $application->setStatus($faker->randomElement(ApplicationStatus::cases()));
            $application->setCreatedAt($questionnaire->getSubmittedAt());
            $application->setUpdatedAt(\DateTimeImmutable::createFromMutable(
                $faker->dateTimeBetween($submittedAt->format('Y-m-d H:i:s'), 'now')
            ));

            // 70% des dossiers rattachés à un compte user, les autres sont "orphelins"
            if ($faker->boolean(70)) {
                $application->setUser($faker->randomElement($users));
            }
            $manager->persist($application);

            // 1/3 dossier a un rendez-vous associé
            if ($faker->boolean(33)) {
                $appointment = new Appointment();
                $appointment->setApplication($application);
                $appointment->setDate(\DateTimeImmutable::createFromMutable(
                    $faker->dateTimeBetween('now', '+1 month')
                ));
                $appointment->setStatus($faker->randomElement(AppointmentStatus::cases()));
                $manager->persist($appointment);
            }
        }
        $manager->flush();
    }
}
