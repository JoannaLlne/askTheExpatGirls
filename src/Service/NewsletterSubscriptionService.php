<?php
namespace App\Service;

use App\Entity\NewsletterSubscriber;
use Doctrine\ORM\EntityManagerInterface;

class NewsletterSubscriptionService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function subscribe(string $email): void
    {
        $existing = $this->em->getRepository(NewsletterSubscriber::class)
            ->findOneBy(['email' => $email]);

        //Si déjà inscrit
        if ($existing) {
            return;
        }

        $subscriber = new NewsletterSubscriber();
        $subscriber->setEmail($email);
        $this->em->persist($subscriber);
    }
}
