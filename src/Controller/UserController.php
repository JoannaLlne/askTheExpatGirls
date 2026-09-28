<?php

namespace App\Controller;

use App\Entity\Application;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\ApplicationRepository;
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
    #[IsGranted('ROLE_USER')]
    #[Route('/user/{id}', name: 'user_profile', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(User $user, ApplicationRepository $applicationRepository): Response
    {
        // Un utilisateur ne peut voir que son propre profil
        if ($user !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'applications' => $applicationRepository->findBy(
                ['user' => $user],
                ['createdAt' => 'DESC']
            ),
        ]);
    }

    //modification de son profil
    #[IsGranted('ROLE_USER')]
    #[Route('/update/{id}', name: 'user_update', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function update(int $id, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($user->getId() !== $id) {
            throw $this->createAccessDeniedException('Only the owner of this account can edit it.');
        }

        $userForm = $this->createForm(UserType::class, $user);
        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $plainPassword = $userForm->get('password')->getData();

            if (!empty($plainPassword)) {
                $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            }

            $em->flush();
            $this->addFlash('success', 'Profil mis à jour.');

            return $this->redirectToRoute('user_profile', ['id' => $user->getId()]);
        }

        return $this->render('user/update_user.html.twig', [
            'userForm' => $userForm,
        ]);
    }
}
