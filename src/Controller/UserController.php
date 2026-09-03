<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('/user/{id}', name: 'user_show', requirements: ['id'=>'\d+'], methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/profil.html.twig', [
            'User' => $user,
        ]);
    }

     #[Route('/createUser', name: 'user_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        // notre entité vide
        $user = new User();

        // notre formulaire, associée à l'entité vide
        $userForm = $this->createForm(UserType::class, $user);

        // récupère les données du form et les injecte dans notre $user
        $userForm->handleRequest($request);

        // si le formulaire est soumis et est valide
        if ($userForm->isSubmitted() && $userForm->isValid()) {

            $roleDefault = array('ROLE_USER');
            $user->setRole($roleDefault);
            $user->setPassword(
                $passwordHasher->hashPassword($user,$userForm->get('password')->getData()));

            // sauvegarde en bdd
            $em->persist($user);
            $em->flush();

            // affiche un message sur la prochaine page si soumission
            $this->addFlash('success', 'Your profile was successfully created.');
            // redirige vers la page de détails de l'idée fraîchement créée
            return $this->redirectToRoute('main_index');

            // return $this->redirectToRoute('user_show', ['id' => $user->getId()]);
        }

        // affiche le formulaire
        return $this->render('user/create_user.html.twig', [
            'userForm' => $userForm,
        ]);
    }


}
