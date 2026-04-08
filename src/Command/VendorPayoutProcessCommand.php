<?php

declare(strict_types=1);

namespace App\Command;

use App\ServiceInterface\Payout\VendorPayoutServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * CLI entrypoint for vendor payout process operations.
 */
#[AsCommand(name: 'app:vendor:payout:process', description: 'Process an existing vendor payout.')]
final class VendorPayoutProcessCommand extends Command
{
    public function __construct(private readonly VendorPayoutServiceInterface $payouts)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('payoutId', InputArgument::REQUIRED)
            ->addOption('json', null, InputOption::VALUE_NONE, 'Emit machine-readable JSON output.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $payoutId = (string) $input->getArgument('payoutId');
        $processed = $this->payouts->process($payoutId);

        if (true === $input->getOption('json')) {
            $output->writeln(json_encode([
                'payoutId' => $payoutId,
                'processed' => $processed,
            ], JSON_THROW_ON_ERROR));

            return Command::SUCCESS;
        }

        $io = new SymfonyStyle($input, $output);

        if ($processed) {
            $io->success(sprintf('Processed payout %s.', $payoutId));

            return Command::SUCCESS;
        }

        $io->warning(sprintf('Payout %s was not processed.', $payoutId));

        return Command::SUCCESS;
    }
}
