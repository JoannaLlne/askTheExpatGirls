<?php

namespace App\Tests\Controller;

use App\Entity\Application;
use App\Entity\Service;
use App\Enum\ApplicationStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class QuestionnaireControllerTest extends WebTestCase
{
    public function testSubmittingValidQuestionnaireCreatesApplication(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        // Creation service, nécessaire pour le menu déroulant du formulaire
        $service = new Service();
        $service->setName('Test service')->setDescription('description test')->setPrice(100);
        $em->persist($service);
        $em->flush();

        // Chargement vrai formulaire (GET), avec vrai token CSRF
        $crawler = $client->request('GET', '/questionnaire');
        $this->assertResponseIsSuccessful();

        // Remplit les champs + soumission
        $form = $crawler->selectButton('Submit')->form([
            'questionnaire[firstName]' => 'Jane',
            'questionnaire[lastName]' => 'Doe',
            'questionnaire[email]' => 'jane.doe@example.com',
            'questionnaire[nationality]' => 'French',
            'questionnaire[situation]' => 'Student',
            'questionnaire[helpReason]' => 'Stay & Immigration',
            'questionnaire[service]' => (string) $service->getId(),
            'questionnaire[additionalInfo]' => 'Test message',
        ]);

        $client->submit($form);

        // Check la redirection vers la page de remerciement
        $this->assertResponseRedirects('/questionnaire/thank-you');

        // Vérifie que dossier (Application) a été créée, avec les bonnes valeurs
        $application = $em->getRepository(Application::class)
            ->findOneBy(['email' => 'jane.doe@example.com']);

        $this->assertNotNull($application);
        $this->assertSame(ApplicationStatus::NEW, $application->getStatus());
        $this->assertSame($service->getId(), $application->getService()->getId());
    }
}
