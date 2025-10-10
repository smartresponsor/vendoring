<?php
declare(strict_types=1);

namespace App\Command;

use App\Repository\OutboxMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:vendor:repair-outbox', description: 'Repair stuck or failed Outbox messages')]
final class VendorRepairOutboxCommand extends Command
{
    public function __construct(
        private readonly OutboxMessageRepository $repo,
        private readonly EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stuck = $this->repo->findBy(['status' => 'failed']);
        $count = 0;

        foreach ($stuck as $msg) {
            $msg->markProcessing();
            $count++;
        }

        $this->em->flush();
        $output->writeln("<info>Repaired $count messages.</info>");
        return Command::SUCCESS;
    }
}
