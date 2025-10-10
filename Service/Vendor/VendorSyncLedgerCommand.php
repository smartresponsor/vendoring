<?php
declare(strict_types=1);

namespace App\CLI\Vendor;

use App\Command\Vendor\SyncVendorLedgerCommand;
use App\CommandBus\Vendor\VendorSyncCommandBus;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:vendor:sync-ledger', description: 'Sync vendor with Ledger')]
final class SyncLedgerCommand extends Command
{
    public function __construct(private readonly VendorSyncCommandBus $bus)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('vendorId', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $vendorId = (int) $input->getArgument('vendorId');
        $this->bus->dispatch(new SyncVendorLedgerCommand($vendorId));
        $output->writeln('Ledger sync dispatched for vendor ' . $vendorId);
        return Command::SUCCESS;
    }
}
