<?php

namespace App\Tests\Repository;

use App\Entity\Application;
use App\Entity\Questionnaire;
use App\Entity\Service;
use App\Enum\ApplicationStatus;
use App\Enum\ServiceCategory;
use App\Enum\Situation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ApplicationRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);

        // Démarre une transaction : tout ce que le test écrit en base
        // sera annulé automatiquement à la fin, dans tearDown()
        $this->em->getConnection()->beginTransaction();
    }

    public function testFindsOrphanApplicationByEmail(): void
    {
        // Création service
        $service = new Service();
        $service->setName('Test service')->setDescription('...')->setPrice(100);
        $this->em->persist($service);

        // Création questionnaire
        $questionnaire = new Questionnaire();
        $questionnaire->setSubmittedAt(new \DateTimeImmutable());
        $questionnaire->setFirstName('Test');
        $questionnaire->setLastName('User');
        $questionnaire->setEmail('orphan@example.com');
        $questionnaire->setNationality('French');
        $questionnaire->setSituation(Situation::STUDENT);
        $questionnaire->setHelpReason(ServiceCategory::STAYIMMIGRATION);
        $this->em->persist($questionnaire);

        // Création dossier
        $application = new Application();
        $application->setEmail('orphan@example.com');
        $application->setService($service);
        $application->setQuestionnaire($questionnaire);
        $application->setStatus(ApplicationStatus::NEW);
        $application->setCreatedAt(new \DateTimeImmutable());
        // user non défini : reste null

        $this->em->persist($application);
        $this->em->flush();

        $repository = $this->em->getRepository(Application::class);
        $results = $repository->findOrphelinsByEmail('orphan@example.com');

        $this->assertCount(1, $results);
        $this->assertNull($results[0]->getUser());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Annule ce que le test a écrit
        // La base de test retrouve son état d'avant le test
        $this->em->getConnection()->rollBack();
        $this->em->close();
    }
}
