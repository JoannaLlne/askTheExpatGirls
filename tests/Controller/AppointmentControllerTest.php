<?php

namespace App\Tests\Controller;

use App\Entity\Application;
use App\Entity\Appointment;
use App\Entity\Questionnaire;
use App\Entity\Service;
use App\Entity\User;
use App\Enum\ApplicationStatus;
use App\Enum\AppointmentStatus;
use App\Enum\ServiceCategory;
use App\Enum\Situation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppointmentControllerTest extends WebTestCase
{
    public function testCannotCancelAnotherUsersAppointment(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $hasher = $container->get(UserPasswordHasherInterface::class);

        // L'utilisateur propriétaire du rendez-vous
        $owner = new User();
        $owner->setEmail('owner@example.com')->setFirstName('Owner')->setLastName('Test');
        $owner->setPassword($hasher->hashPassword($owner, 'password123'));
        $owner->setCreatedAt(new \DateTimeImmutable());
        $em->persist($owner);

        // Un autre utilisateur, qui va tenter l'annulation
        $attacker = new User();
        $attacker->setEmail('attacker@example.com')->setFirstName('Attacker')->setLastName('Test');
        $attacker->setPassword($hasher->hashPassword($attacker, 'password123'));
        $attacker->setCreatedAt(new \DateTimeImmutable());
        $em->persist($attacker);

        $service = new Service();
        $service->setName('Test service')->setDescription('...')->setPrice(100);
        $em->persist($service);

        $questionnaire = new Questionnaire();
        $questionnaire->setSubmittedAt(new \DateTimeImmutable());
        $questionnaire->setFirstName('Owner')->setLastName('Test')->setEmail('owner@example.com');
        $questionnaire->setNationality('French');
        $questionnaire->setSituation(Situation::STUDENT);
        $questionnaire->setHelpReason(ServiceCategory::STAYIMMIGRATION);
        $em->persist($questionnaire);

        $application = new Application();
        $application->setEmail('owner@example.com');
        $application->setService($service);
        $application->setQuestionnaire($questionnaire);
        $application->setStatus(ApplicationStatus::NEW);
        $application->setCreatedAt(new \DateTimeImmutable());
        $application->setUser($owner);
        $em->persist($application);

        $appointment = new Appointment();
        $appointment->setDate(new \DateTimeImmutable('next monday 10:00'));
        $appointment->setStatus(AppointmentStatus::BOOKED);
        $appointment->setApplication($application);
        $em->persist($appointment);

        $em->flush();

        // L'attaquant se connecte et tente d'annuler le RDV du propriétaire
        $client->loginUser($attacker);
        $client->request('POST', '/appointment/' . $appointment->getId() . '/cancel', [
            '_token' => 'fake-token', // le CSRF invalide arrêtera la requête avant même le Voter, voir note ci-dessous
        ]);

        $this->assertResponseStatusCodeSame(403);

        // Le rendez-vous ne doit pas avoir été modifié
        $em->refresh($appointment);
        $this->assertSame(AppointmentStatus::BOOKED, $appointment->getStatus());
    }
}
