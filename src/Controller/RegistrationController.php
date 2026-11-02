<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\ApplicationRepository;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher,
                             EntityManagerInterface $entityManager,
                             ApplicationRepository $applicationRepository): Response
    {
        $user = new User();
        $createdAt = new \DateTimeImmutable();
        $user->setCreatedAt($createdAt);

        $registerForm = $this->createForm(RegistrationFormType::class, $user);
        $registerForm->handleRequest($request);

        if ($registerForm->isSubmitted() && $registerForm->isValid()) {

            /** @var string $plainPassword */
            $plainPassword = $registerForm->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('joanna.lalanne2026@campus-eni.fr', 'Ask the Expat Girls'))
                    ->to((string) $user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            // Rattachement automatique des dossiers existants
            $applicationsOrphelines = $applicationRepository->findOrphelinsByEmail($user->getEmail());
            foreach ($applicationsOrphelines as $application) {
                $application->setUser($user);
            }
            if (count($applicationsOrphelines) > 0) {
                $entityManager->flush();
            }

            $this->addFlash(
                'success',
                'Your account has been created. Please check your email to activate it.'
            );

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $registerForm,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(
        Request $request,
        TranslatorInterface $translator,
        UserRepository $userRepository
    ): Response {

        $id = $request->query->get('id');

        if (null === $id) {
            $this->addFlash('verify_email_error', 'Invalid verification link.');

            return $this->redirectToRoute('app_login');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            $this->addFlash('verify_email_error', 'User not found.');

            return $this->redirectToRoute('app_login');
        }

        try {
            $this->emailVerifier->handleEmailConfirmation($request);

        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash(
                'verify_email_error',
                $translator->trans($exception->getReason(), [], 'VerifyEmailBundle')
            );

            return $this->redirectToRoute('app_login');
        }

        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_login');
    }
}
