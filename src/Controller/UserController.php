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
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class UserController extends AbstractController
{
    #[Route('/user/{id}', name: 'user_profile', requirements: ['id'=>'\d+'], methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/profile.html.twig', [
            'user' => $user,
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

    //modification de son profil
    #[Route('/update/{id}', name: 'user_update', requirements: ['id'=>'\d+'], methods: ['GET','POST'])]
    #[IsGranted('ROLE_USER')]
    public function update(int $id, UserRepository $userRepository, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        //récupération de l'utilisateur connecté
        /** @var User|null $user */
        $user = $this->getUser();

        //on vérifie que le profil est bien celui de l'utilisateur connecté
        if ($user->getId() !== $id) {
            throw $this->createAccessDeniedException('Only the owner of this account can edit it.');
        }
        // récupère user en fonction de l'id présent dans l'URL
        $user = $userRepository->find($id);

        // s'il n'existe pas en bdd, on déclenche une erreur 404
        if (!$user) {
            throw $this->createNotFoundException('This user does not exist.');
        }
        // notre formulaire, associée à l'entité vide
        $userForm = $this->createForm(UserType::class, $user);
        // récupère les données du form et les injecter
        $userForm->handleRequest($request);

        // si le formulaire est soumis et valide
        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $user->setPassword(
                $passwordHasher->hashPassword($user,$userForm->get('password')->getData()));

            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('user_profile', ['id' => $user->getId()]);
        }

        // affiche le formulaire
        return $this->render('user/create_user.html.twig', [
            'userForm' => $userForm,
        ]);
    }

}
