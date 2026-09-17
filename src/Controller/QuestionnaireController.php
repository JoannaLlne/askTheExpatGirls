<?php

namespace App\Controller;

use App\Entity\Questionnaire;
use App\Form\QuestionnaireType;
use App\Mailer\AdminNotificationHandler;
use App\Mailer\AdminNotificationMailer;
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
                           AdminNotificationMailer $adminNotif): Response
    {
        // Entité vide
        $questionnaire = new Questionnaire();
        $createdAt = new \DateTimeImmutable();
        $questionnaire->setSubmittedAt($createdAt);

        // Le formulaire, associée à l'entité vide
        $questionnaireForm = $this->createForm(QuestionnaireType::class, $questionnaire);

        // récupère les données du form et les injecte dans notre $questionnaire
        $questionnaireForm->handleRequest($request);

        // Si formulaire soumis et valide
        if ($questionnaireForm->isSubmitted() && $questionnaireForm->isValid()) {

            // sauvegarde en bdd
            $em->persist($questionnaire);

            // Seulement si la case est cochée : on inscrit aussi dans subscribers
            if ($questionnaireForm->get('newsletter')->getData() === true) {
                $newsletterService->subscribe($questionnaire->getEmail());
            }

            $em->flush();
            //Envoi notification
            try {
                $adminNotif->notifyFormSubmitted();
                dump('Email envoyé avec succès');
            } catch (\Throwable $e) {
                dump('ERREUR : ' . $e->getMessage());
            }

            // affiche un message sur la prochaine page si soumission
            $this->addFlash('success', 'The questionnaire was successfully sent');
            return $this->redirectToRoute('contact_page');
        }
        // affiche le formulaire
        return $this->render('questionnaire/questionnaire.html.twig', [
            'questionnaireForm' => $questionnaireForm,
        ]);
    }
}

