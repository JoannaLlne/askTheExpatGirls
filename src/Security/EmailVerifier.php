<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class EmailVerifier
{
    public function __construct(
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private MailerInterface $mailer,
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
    ) {
    }

    public function sendEmailConfirmation(string $verifyEmailRouteName, User $user, TemplatedEmail $email): void
    {
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            $verifyEmailRouteName,
            (string) $user->getId(),
            (string) $user->getEmail(),
            ['id' => $user->getId()]
        );

        $context = $email->getContext();
        $context['signedUrl'] = $signatureComponents->getSignedUrl();
        $context['expiresAtMessageKey'] = $signatureComponents->getExpirationMessageKey();
        $context['expiresAtMessageData'] = $signatureComponents->getExpirationMessageData();

        $email->context($context);

        $this->mailer->send($email);
    }

    /**
     * @throws VerifyEmailExceptionInterface
     */
    public function handleEmailConfirmation(Request $request): void
    {
        // Récupération de l'ID transmis dans le lien signé
        $userId = $request->query->get('id');

        if (!$userId) {
            throw new \InvalidArgumentException('Missing user ID.');
        }

        $user = $this->userRepository->find($userId);

        if (!$user) {
            throw new \InvalidArgumentException('User not found.');
        }

        // Validation du lien signé
        $this->verifyEmailHelper->validateEmailConfirmationFromRequest(
            $request,
            (string) $user->getId(),
            (string) $user->getEmail()
        );

        // Activation du compte
        $user->setIsVerified(true);

        $this->entityManager->flush();
    }
}
