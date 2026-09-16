<?php

namespace App\Controller;

use App\Entity\Questionnaire;
use App\Entity\User;
use App\Form\QuestionnaireType;
use App\Repository\QuestionnaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class QuestionnaireController extends AbstractController
{

    public function __construct(
        #[Autowire('%admin_email%')]
        private string $adminEmail,
    ) {}

    #[Route('/questionnaire', name: 'new_questionnaire', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em, MailerInterface $mailer): Response
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
            $em->flush();

            // Création de l'email
            $email = (new Email())
                ->from($this->adminEmail) // Expéditeur issu du formulaire
                ->to($this->adminEmail) // Destinataire
                ->subject('New form')
                ->text('Someone filled out a new form. Check your Dashboard');
            // Envoi de l'email
            $mailer->send($email);

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

