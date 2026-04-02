<?php

declare(strict_types=1);

namespace App\Command;

use App\DTO\Payout\CreatePayoutDTO;
use App\ServiceInterface\Payout\VendorPayoutServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:vendor:payout:create', description: 'Create a vendor payout from payout threshold inputs.')]
final class VendorPayoutCreateCommand extends Command
{
    public function __construct(private readonly VendorPayoutServiceInterface $payouts)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('tenantId', InputArgument::REQUIRED)
            ->addArgument('vendorId', InputArgument::REQUIRED)
            ->addArgument('currency', InputArgument::REQUIRED)
            ->addArgument('thresholdCents', InputArgument::REQUIRED)
            ->addArgument('retentionFeePercent', InputArgument::REQUIRED)
            ->addOption('process', null, InputOption::VALUE_NONE, 'Process the payout immediately after creation.')
            ->addOption('json', null, InputOption::VALUE_NONE, 'Emit machine-readable JSON output.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dto = new CreatePayoutDTO(
            tenantId: (string) $input->getArgument('tenantId'),
            vendorId: (string) $input->getArgument('vendorId'),
            currency: strtoupper((string) $input->getArgument('currency')),
            thresholdCents: (int) $input->getArgument('thresholdCents'),
            retentionFeePercent: (float) $input->getArgument('retentionFeePercent'),
        );

        $payoutId = $this->payouts->create($dto);
        $processed = false;

        if (null !== $payoutId && true === $input->getOption('process')) {
            $processed = $this->payouts->process($payoutId);
        }

        if (true === $input->getOption('json')) {
            $output->writeln(json_encode([
                'created' => null !== $payoutId,
                'payoutId' => $payoutId,
                'processed' => $processed,
            ], JSON_THROW_ON_ERROR));

            return Command::SUCCESS;
        }

        $io = new SymfonyStyle($input, $output);

        if (null === $payoutId) {
            $io->warning('No payout created. Threshold was not reached.');

            return Command::SUCCESS;
        }

        $io->success(sprintf('Created payout %s.', $payoutId));

        if (true === $input->getOption('process')) {
            if ($processed) {
                $io->success(sprintf('Processed payout %s.', $payoutId));
            } else {
                $io->warning(sprintf('Payout %s was created but not processed.', $payoutId));
            }
        }

        return Command::SUCCESS;
    }
}
