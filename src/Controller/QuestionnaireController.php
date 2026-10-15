<?php

namespace App\Controller;

use App\Entity\Application;
use App\Entity\Questionnaire;
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
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class QuestionnaireController extends AbstractController
{

    #[Route('/questionnaire', name: 'new_questionnaire', methods: ['GET', 'POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        NewsletterSubscriptionService $newsletterService,
        AdminNotificationMailer $adminNotif,
        ServiceRepository $serviceRepository,
        ExpatPackagesRepository $expatPackagesRepository,
        ValidatorInterface $validator
    ): Response {
        // Entité vide
        $questionnaire = new Questionnaire();
        $questionnaire->setSubmittedAt(new \DateTimeImmutable());

        // Le formulaire, associée à l'entité vide
        $questionnaireForm = $this->createForm(QuestionnaireType::class, $questionnaire, [
            //Trouver les "Services" utilisés dans des "Applications"
            'services' => $serviceRepository->findActiveService(),
            'packages' => $expatPackagesRepository->findActivePackage(),
        ]);

        if ($user = $this->getUser()) {
            $questionnaireForm->get('firstName')->setData($user->getFirstName());
            $questionnaireForm->get('lastName')->setData($user->getLastName());
            $questionnaireForm->get('email')->setData($user->getEmail());
        }

        // récupère les données du form et les injecte dans notre $questionnaire
        $questionnaireForm->handleRequest($request);

        // Si formulaire soumis et valide
        if ($questionnaireForm->isSubmitted() && $questionnaireForm->isValid()) {
            // Si honeypot rempli = bot : on fait semblant que tout s'est bien passé
            if (!empty($questionnaireForm->get('website')->getData())) {
                return $this->redirectToRoute('questionnaire_thanks');
            }

            // sauvegarde du questionnaire en bdd
            $em->persist($questionnaire);

            // Seulement si la case est cochée : on inscrit aussi dans subscribers
            if ($questionnaireForm->get('newsletter')->getData() === true) {
                $newsletterService->subscribe($questionnaire->getEmail());
            }

            $application = new Application();
            $application->setEmail($questionnaireForm->get('email')->getData());
            $application->setQuestionnaire($questionnaire);
            $application->setStatus(ApplicationStatus::NEW);
            $application->setCreatedAt(new \DateTimeImmutable());
            $application->setUpdatedAt(new \DateTimeImmutable());

            // Récupère service/package choisi
            $chosen = $questionnaireForm->get('serviceOrPackage')->getData();
            [$type, $id] = explode('-', $chosen);

            if ($type === 'service') {
                $application->setService($serviceRepository->find($id));
            } else {
                $application->setExpatPackage($expatPackagesRepository->find($id));
            }

            // Validation manuelle : déclenche le Callback service/package
            $errors = $validator->validate($application);
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
                return $this->redirectToRoute('new_questionnaire');
            }

            $em->persist($application);
            $em->flush();

            // Envoi notification
            try {
                $adminNotif->notifyFormSubmitted();
            } catch (\Throwable $e) {
                // on pourra remplacer par un vrai logger plus tard si besoin
            }

            // affiche un message sur la prochaine page si soumission du formulaire
            $request->getSession()->set('inquiry_submitted', true);
            return $this->redirectToRoute('questionnaire_thanks');
        }

        return $this->render('questionnaire/questionnaire.html.twig', [
            'questionnaireForm' => $questionnaireForm,
            'user' => $user ?? null,
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

