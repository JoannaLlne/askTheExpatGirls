<?php

namespace App\Controller;

use App\Entity\Questionnaire;
use App\Entity\User;
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

        if ($user = $this->getUser()) {
            $questionnaireForm->get('firstName')->setData($user->getFirstName());
            $questionnaireForm->get('lastName')->setData($user->getLastName());
        }


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

            // affiche un message sur la prochaine page si soumission du formulaire
            $request->getSession()->set('inquiry_submitted', true);
            return $this->redirectToRoute('request_thanks');
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

