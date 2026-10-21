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

        // Un service existant, nécessaire pour le menu déroulant du formulaire
        $service = new Service();
        $service->setName('Test service')->setDescription('...')->setPrice(100);
        $em->persist($service);
        $em->flush();

        // 1. On charge le vrai formulaire (GET), avec son vrai token CSRF
        $crawler = $client->request('GET', '/questionnaire');
        $this->assertResponseIsSuccessful();

        // 2. On remplit les champs et on soumet
        $form = $crawler->selectButton('Submit')->form([
            'questionnaire[firstName]' => 'Jane',
            'questionnaire[lastName]' => 'Doe',
            'questionnaire[email]' => 'jane.doe@example.com',
            'questionnaire[nationality]' => 'French',
            'questionnaire[situation]' => 'Student',
            'questionnaire[helpReason]' => 'Stay & Immigration',
            'questionnaire[serviceOrPackage]' => 'service-' . $service->getId(),
            'questionnaire[additionalInfo]' => 'Test message',
        ]);

        $client->submit($form);

        // 3. On vérifie la redirection vers la page de remerciement
        $this->assertResponseRedirects('/questionnaire/thank-you');

        // 4. On vérifie que l'Application a bien été créée, avec les bonnes valeurs
        $application = $em->getRepository(Application::class)
            ->findOneBy(['email' => 'jane.doe@example.com']);

        $this->assertNotNull($application);
        $this->assertSame(ApplicationStatus::NEW, $application->getStatus());
        $this->assertSame($service->getId(), $application->getService()->getId());
        $this->assertNull($application->getExpatPackage());
    }
}
