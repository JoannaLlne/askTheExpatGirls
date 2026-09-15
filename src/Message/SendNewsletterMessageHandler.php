<?php
namespace App\Message;

use App\Message\SendNewsletterMessage;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

#[AsMessageHandler]
class SendNewsletterMessageHandler
{
    public function __construct(
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public function __invoke(SendNewsletterMessage $message): void
    {
        $unsubscribeUrl = $this->urlGenerator->generate(
            'newsletter_unsubscribe',
            ['token' => $message->unsubscribeToken],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $email = (new TemplatedEmail())
            ->from('joanna.lalanne2026@campus-eni.fr')
            ->to($message->email)
            ->subject($message->subject)
            ->htmlTemplate('email/newsletter.html.twig')
            ->context([
                'subject' => $message->subject,
                'content' => $message->content,
                'unsubscribe_url' => $unsubscribeUrl,
                ]);
        $this->mailer->send($email);
    }
}
