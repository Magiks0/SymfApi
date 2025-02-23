<?php

namespace App\Command;

use App\Repository\UserRepository;
use App\Service\MailerService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Scheduler\Attribute\AsCronTask;

#[AsCommand(
    name: 'app:send-email',
    description: 'Send email to newsletter subscribers',
)]
class SendEmailCommand extends Command
{
    public function __construct(
        private readonly MailerService $mailerService,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        try {
            $this->mailerService->sendNewsletterEmail();
        } catch(\Exception $e) {
            $io->error('Error : '.$e->getMessage());
        }

        $io->success('Daily mail send !');

        return Command::SUCCESS;
    }
}
