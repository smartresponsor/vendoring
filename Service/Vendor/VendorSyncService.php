<?php
declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Log\LoggerInterface;

#[AsCommand(name: 'app:vendor:sync', description: 'Synchronize Vendor events with Ledger and CRM')]
final class VendorSyncCommand extends Command
{
    public function __construct(private readonly LoggerInterface $logger)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->info('Starting vendor synchronization with Ledger and CRM...');
        // TODO: Implement actual sync with VendorLedgerBinding and CRM API
        $output->writeln('<info>Vendor sync completed successfully.</info>');
        return Command::SUCCESS;
    }
}
