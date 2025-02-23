<?php

namespace App\Scheduler\Handler;

use App\Scheduler\Message\SendEmailMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use App\Service\MailerService;

#[AsMessageHandler]
readonly class SendEmailMessageHandler
{
    public function __construct(private MailerService $mailerService)
    {
    }

    public function __invoke(SendEmailMessage $message): void
    {
        $this->mailerService->sendNewsletterEmail();
    }

}
