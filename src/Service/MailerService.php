<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\VideoGameRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService
{
    private MailerInterface $mailer;
    private VideoGameRepository $videoGameRepository;
    private userRepository $userRepository;

    public function __construct(MailerInterface $mailer, VideoGameRepository $videoGameRepository, UserRepository $userRepository)
    {
        $this->mailer = $mailer;
        $this->videoGameRepository = $videoGameRepository;
        $this->userRepository = $userRepository;
    }

    public function sendEmail(string $to, string $subject, string $content): void
    {
        $email = (new Email())
            ->from('no-reply@example.fr')
            ->to($to)
            ->subject($subject)
            ->text($content);

        $this->mailer->send($email);
    }

    public function sendNewsletterEmail(): void
    {
        $subscribers = $this->userRepository->findBy(['subscribedToNewsletter' => true]);
        foreach ($subscribers as $subscriber) {
            $email = (new TemplatedEmail())
                ->from('no-reply@example.fr')
                ->to($subscriber->getEmail())
                ->subject('Les sorties de la semaine')
                ->htmlTemplate('mailer/mail.html.twig')
                ->context([
                    'games' => $this->videoGameRepository->find7daysReleaseGames(),
                ]);

            $this->mailer->send($email);
        }

    }
}
