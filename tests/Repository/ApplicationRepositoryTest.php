<?php
namespace App\Tests\Repository;

use App\Entity\Application;
use App\Entity\Questionnaire;
use App\Entity\Service;
use App\Enum\ApplicationStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ApplicationRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }


    public function testFindsOrphanApplicationByEmail(): void
    {
        $service = new Service();
        $service->setName('Test service')->setDescription('...')->setPrice(100);
        $this->em->persist($service);

        $questionnaire = new Questionnaire();
        $questionnaire->setSubmittedAt(new \DateTimeImmutable());
        $questionnaire->setFirstName('Test');
        $questionnaire->setLastName('User');
        $questionnaire->setEmail('orphan@example.com');
        $questionnaire->setNationality('French');
        $questionnaire->setSituation('STUDENT'); // ou la valeur attendue par ton enum/champ
        $questionnaire->setHelpReason('STAY_AND_IMMIGRATION'); // idem, adapte à ce que ton champ attend réellement
        $this->em->persist($questionnaire);

        // ... autres champs requis par ton entité
        $this->em->persist($questionnaire);

        $application = new Application();
        $application->setEmail('orphan@example.com');
        $application->setService($service);
        $application->setQuestionnaire($questionnaire);
        $application->setStatus(ApplicationStatus::NEW);
        $application->setCreatedAt(new \DateTimeImmutable());

        // user volontairement non défini : reste null
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
        $this->em->close();
    }
}
