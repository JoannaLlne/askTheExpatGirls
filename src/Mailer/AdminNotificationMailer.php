<?php

namespace App\Mailer;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class AdminNotificationMailer
{
    private array $adminEmails;

    public function __construct(
        private MailerInterface $mailer,
        string $adminEmails // Injecté via services.yaml
    ) {
        //Transforme string en tableau si plusieurs email
        $this->adminEmails = explode(',', $adminEmails);
    }

    public function notifyFormSubmitted(): void
    {
        $email = (new Email())
            ->from('joanna.lalanne2026@campus-eni.fr')
            ->to(...$this->adminEmails)
            ->subject('New form submission')
            ->text('Someone filled out a form. Go check your dashboard.')
        ;
        $this->mailer->send($email);
    }
}
