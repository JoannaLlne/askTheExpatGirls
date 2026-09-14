<?php

namespace App\Controller;

use App\Entity\Questionnaire;
use App\Entity\User;
use App\Form\QuestionnaireType;
use App\Repository\QuestionnaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class QuestionnaireController extends AbstractController
{

/* [IsGranted('ROLE_USER')]
    #[Route('/questionnaire', name: 'user_questionnaire')]
    public function index(Questionnaire $questionnaire): Response
    {
        return $this->render('questionnaire/questionnaire.html.twig', [
            'questionnaire' => $questionnaire,
        ]);
    } */


    #[Route('/questionnaire', name: 'new_questionnaire', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {

           /*$existingQuestionnaire = $em->getRepository(Questionnaire::class)->findOneBy(['user' => $user]);

            if ($existingQuestionnaire) {
                $this->addFlash('info', 'Sorry but you already sent your infos!');
                return $this->redirectToRoute('main_index');
            }*/

        // notre entité vide
        $questionnaire = new Questionnaire();
        $createdAt = new \DateTimeImmutable();
        $questionnaire->setSubmittedAt($createdAt);

        // notre formulaire, associée à l'entité vide
        $questionnaireForm = $this->createForm(QuestionnaireType::class, $questionnaire);

        // récupère les données du form et les injecte dans notre $questionnaire
        $questionnaireForm->handleRequest($request);

        // si le formulaire est soumis et est valide
        if ($questionnaireForm->isSubmitted() && $questionnaireForm->isValid()) {

            // sauvegarde en bdd
            $em->persist($questionnaire);
            $em->flush();

            // affiche un message sur la prochaine page si soumission
            $this->addFlash('success', 'The questionnaire was successfully sent');
            // redirige vers la page de détails de l'idée fraîchement créée
            return $this->redirectToRoute('user_profile', ['id' => $user->getId()]);
        }
        // affiche le formulaire
        return $this->render('questionnaire/questionnaire.html.twig', [
            'questionnaireForm' => $questionnaireForm,
        ]);
    }
}

