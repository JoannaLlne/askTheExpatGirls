<?php

namespace App\Controller;

use App\Entity\Application;
use App\Entity\Questionnaire;
use App\Entity\User;
use App\Enum\ApplicationStatus;
use App\Form\QuestionnaireType;
use App\Mailer\AdminNotificationMailer;
use App\Repository\ExpatPackagesRepository;
use App\Repository\ServiceRepository;
use App\Service\NewsletterSubscriptionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class QuestionnaireController extends AbstractController
{

    #[Route('/questionnaire', name: 'new_questionnaire', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em, NewsletterSubscriptionService $newsletterService,
                           AdminNotificationMailer $adminNotif,
                           ServiceRepository $serviceRepository, ExpatPackagesRepository $expatPackagesRepository): Response
    {
        // Entité vide
        $questionnaire = new Questionnaire();
        $createdAt = new \DateTimeImmutable();
        $questionnaire->setSubmittedAt($createdAt);

        // Le formulaire, associée à l'entité vide
        $questionnaireForm = $this->createForm(QuestionnaireType::class, $questionnaire, [
            'service'=>$serviceRepository->findAll(),
            'expatPackages'=>$expatPackagesRepository->findAll(),
        ]);

        if ($user = $this->getUser()) {
            $questionnaireForm->get('firstName')->setData($user->getFirstName());
            $questionnaireForm->get('lastName')->setData($user->getLastName());
            $questionnaireForm->get('email')->setData($user->getEmail());
        }

        // récupère les données du form et les injecte dans notre $questionnaire
        $questionnaireForm->handleRequest($request);

        $logFile = __DIR__ . '/../../var/log/debug_questionnaire.log';

        if ($questionnaireForm->isSubmitted()) {
            file_put_contents($logFile, "[" . date('H:i:s') . "] Formulaire soumis\n", FILE_APPEND);
            file_put_contents($logFile, "Valide : " . ($questionnaireForm->isValid() ? 'oui' : 'non') . "\n", FILE_APPEND);

            if (!$questionnaireForm->isValid()) {
                foreach ($questionnaireForm->getErrors(true) as $error) {
                    file_put_contents($logFile, "Erreur : " . $error->getMessage() . " (champ : " . $error->getOrigin()?->getName() . ")\n", FILE_APPEND);
                }
            }
        } else {
            file_put_contents($logFile, "[" . date('H:i:s') . "] Formulaire NON soumis (isSubmitted = false)\n", FILE_APPEND);
        }

        // Si formulaire soumis et valide
        if ($questionnaireForm->isSubmitted() && $questionnaireForm->isValid()) {
            // Si le honeypot est rempli, c'est un bot : on fait semblant que tout s'est bien passé
            if (!empty($questionnaireForm->get('website')->getData())) {
                return $this->redirectToRoute('questionnaire_thanks');
            }

            // sauvegarde du questionnaire en bdd
            $em->persist($questionnaire);

            // Seulement si la case est cochée : on inscrit aussi dans subscribers
            if ($questionnaireForm->get('newsletter')->getData() === true) {
                $newsletterService->subscribe($questionnaire->getEmail());
            }

            $chosen = $questionnaireForm->get('serviceOrPackage')->getData(); // ex: "service-3" ou "package-1"
            [$type, $id] = explode('-', $chosen);


            $application = new Application;
            $application->setEmail($questionnaireForm->get('email')->getData()); // champ ajouté au formulaire)
            $application->setQuestionnaire($questionnaire);
            $application->setService($questionnaireForm->get('service')->getData());            $application->setStatus(ApplicationStatus::NEW);
            $application->setCreatedAt(new \DateTimeImmutable());
            $application->setUpdatedAt(new \DateTimeImmutable());

            $chosen = $questionnaireForm->get('serviceOrPackage')->getData(); // ex: "service-3" ou "package-1"
            [$type, $id] = explode('-', $chosen);

            if ($type === 'service') {
                $application->setService($serviceRepository->find($id));
            } else {
                $application->setExpatPackage($expatPackagesRepository->find($id));
            }

            //placer le questionnaire dans le dossier
            $application->setQuestionnaire($questionnaire);
            // sauvegarde de dossier en bdd
            $em->persist($application);
            $em->flush();

            //Envoi notification
            try {
                $adminNotif->notifyFormSubmitted();
                dump('Email envoyé avec succès');
            } catch (\Throwable $e) {
                dump('ERREUR : ' . $e->getMessage());
            }

            // affiche un message sur la prochaine page si soumission du formulaire
            $request->getSession()->set('inquiry_submitted', true);
            return $this->redirectToRoute('questionnaire_thanks');
        }
        // affiche le formulaire
        return $this->render('questionnaire/questionnaire.html.twig', [
            'questionnaireForm' => $questionnaireForm,
            'user'=>$user,
        ]);
    }

    // src/Controller/QuestionnaireController.php
    #[Route('/questionnaire/thank-you', name: 'questionnaire_thanks')]
    public function thanks(Request $request): Response
    {
         $session = $request->getSession();

        // accès direct à l'URL sans avoir soumis le questionnaire : retour au formulaire
        if (!$session->remove('inquiry_submitted')) {
            return $this->redirectToRoute('contact_page');
        }

        return $this->render('questionnaire/thanks.html.twig');
    }
}

