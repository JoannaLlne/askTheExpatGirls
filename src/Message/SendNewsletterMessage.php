<?php
namespace App\Message;

class SendNewsletterMessage
{
    public function __construct(
        public readonly string $email,
        public readonly string $subject,
        public readonly string $content,
        public readonly string $unsubscribeToken,
    ) {}
}
