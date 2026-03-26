<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Command;

use App\RepositoryInterface\Payout\PayoutRepositoryInterface;
use App\ServiceInterface\Payout\VendorPayoutRequestServiceInterface;
use App\ServiceInterface\Payout\VendorPayoutServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:vendor:payout:create',
    description: 'Create payout from vendor available balance and print payout state',
)]
final class VendorPayoutCreateCommand extends Command
{
    public function __construct(
        private readonly VendorPayoutRequestServiceInterface $requestService,
        private readonly VendorPayoutServiceInterface $payoutService,
        private readonly PayoutRepositoryInterface $payoutRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('vendorId', null, InputOption::VALUE_REQUIRED, 'Vendor ID')
            ->addOption('currency', null, InputOption::VALUE_OPTIONAL, 'Currency', 'USD')
            ->addOption('thresholdCents', null, InputOption::VALUE_OPTIONAL, 'Minimum balance in cents required to create payout', '1000')
            ->addOption('retentionFeePercent', null, InputOption::VALUE_OPTIONAL, 'Retention fee percent as decimal fraction', '0.05')
            ->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Output format: text|json', 'text');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $payload = [
            'vendorId' => $input->getOption('vendorId'),
            'currency' => $input->getOption('currency'),
            'thresholdCents' => $input->getOption('thresholdCents'),
            'retentionFeePercent' => $input->getOption('retentionFeePercent'),
        ];

        $format = (string) $input->getOption('format');

        try {
            $dto = $this->requestService->toCreateDto($payload);
        } catch (\InvalidArgumentException $exception) {
            $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));

            return Command::FAILURE;
        }

        $payoutId = $this->payoutService->create($dto);

        if (null === $payoutId) {
            $output->writeln('NO_PAYOUT: balance below threshold');

            return Command::SUCCESS;
        }

        $payout = $this->payoutRepository->byId($payoutId);

        if (null === $payout) {
            $output->writeln('<error>Payout was created but cannot be loaded from repository.</error>');

            return Command::FAILURE;
        }

        $normalized = $this->requestService->normalizePayout($payout);

        if ('json' === $format) {
            $output->writeln((string) json_encode($normalized, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

            return Command::SUCCESS;
        }

        $output->writeln(sprintf(
            'PAYOUT_CREATED id=%s vendorId=%s currency=%s grossCents=%d feeCents=%d netCents=%d status=%s',
            $normalized['id'],
            $normalized['vendorId'],
            $normalized['currency'],
            $normalized['grossCents'],
            $normalized['feeCents'],
            $normalized['netCents'],
            $normalized['status'],
        ));

        return Command::SUCCESS;
    }
}
