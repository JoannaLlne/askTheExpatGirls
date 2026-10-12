<?php

namespace App\EventSubscriber;

use App\Repository\ApplicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

//A chaque connexion user, recherche si dossier avec son email
class LinkApplicationToUser implements EventSubscriberInterface
{
    public function __construct(
        private ApplicationRepository $applicationRepository,
        private EntityManagerInterface $entityManager
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        $dossiersOrphelins = $this->applicationRepository->findOrphelinsByEmail($user->getUserIdentifier());

        foreach ($dossiersOrphelins as $dossier) {
            $dossier->setUser($user);
        }

        if (count($dossiersOrphelins) > 0) {
            $this->entityManager->flush();
        }
    }
}
