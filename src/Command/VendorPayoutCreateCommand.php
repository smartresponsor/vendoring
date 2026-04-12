<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Command;

use App\Command\Support\CommandJsonEncoder;
use App\Command\Support\CommandOutputFormat;
use App\Command\Support\CommandResultEmitter;
use App\Command\Support\CommandResultEmitterInterface;
use App\RepositoryInterface\Payout\PayoutRepositoryInterface;
use App\ServiceInterface\Payout\VendorPayoutRequestServiceInterface;
use App\ServiceInterface\Payout\VendorPayoutServiceInterface;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

#[AsCommand(
    name: 'app:vendor:payout:create',
    description: 'Create payout from vendor available balance and print payout state',
)]
final class VendorPayoutCreateCommand extends Command
{
    private readonly VendorPayoutRequestServiceInterface $requestService;
    private readonly VendorPayoutServiceInterface $payoutService;
    private readonly PayoutRepositoryInterface $payoutRepository;
    private readonly CommandResultEmitterInterface $commandResultEmitter;

    public function __construct(
        VendorPayoutRequestServiceInterface $requestService,
        VendorPayoutServiceInterface $payoutService,
        PayoutRepositoryInterface $payoutRepository,
        ?CommandResultEmitterInterface $commandResultEmitter = null,
    ) {
        $this->requestService = $requestService;
        $this->payoutService = $payoutService;
        $this->payoutRepository = $payoutRepository;
        $this->commandResultEmitter = $commandResultEmitter ?? self::defaultCommandResultEmitter();
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();
        $this
            ->addOption('tenantId', null, InputOption::VALUE_REQUIRED, 'Tenant ID')
            ->addOption('vendorId', null, InputOption::VALUE_REQUIRED, 'Vendor ID')
            ->addOption('currency', null, InputOption::VALUE_OPTIONAL, 'Currency', 'USD')
            ->addOption('thresholdCents', null, InputOption::VALUE_OPTIONAL, 'Minimum balance in cents required to create payout', '1000')
            ->addOption('retentionFeePercent', null, InputOption::VALUE_OPTIONAL, 'Retention fee percent as decimal fraction', '0.05')
            ->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Output format: text|json', 'text');
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $payload = [
            'tenantId' => $input->getOption('tenantId'),
            'vendorId' => $input->getOption('vendorId'),
            'currency' => $input->getOption('currency'),
            'thresholdCents' => $input->getOption('thresholdCents'),
            'retentionFeePercent' => $input->getOption('retentionFeePercent'),
        ];

        $format = CommandOutputFormat::normalize($input->getOption('format'));

        try {
            $dto = $this->requestService->toCreateDto($payload);
            $payoutId = $this->payoutService->create($dto);
        } catch (InvalidArgumentException $exception) {
            $this->commandResultEmitter->emitError($output, $format, 'invalid', $exception->getMessage(), [
                'payload' => $payload,
            ]);

            return Command::FAILURE;
        } catch (Throwable $throwable) {
            $this->commandResultEmitter->emitThrowableError($output, $format, 'failed', 'Failed to create payout', $throwable, [
                'payload' => $payload,
            ]);

            return Command::FAILURE;
        }

        if (null === $payoutId) {
            if (CommandOutputFormat::isJson($format)) {
                return $this->commandResultEmitter->emitJson($output, [
                    'status' => 'skipped',
                    'reason' => 'balance_below_threshold',
                    'payload' => $payload,
                ]) ? Command::SUCCESS : Command::FAILURE;
            }

            $output->writeln('NO_PAYOUT: balance below threshold');

            return Command::SUCCESS;
        }

        try {
            $payout = $this->payoutRepository->byId($payoutId);
        } catch (Throwable $throwable) {
            $this->commandResultEmitter->emitThrowableError($output, $format, 'failed', 'Failed to load payout', $throwable, [
                'payoutId' => $payoutId,
                'payload' => $payload,
            ]);

            return Command::FAILURE;
        }

        if (null === $payout) {
            $this->commandResultEmitter->emitError($output, $format, 'failed', 'Payout was created but cannot be loaded from repository.', [
                'payoutId' => $payoutId,
                'payload' => $payload,
            ]);

            return Command::FAILURE;
        }

        $normalized = $this->requestService->normalizePayout($payout);

        if (CommandOutputFormat::isJson($format)) {
            return $this->commandResultEmitter->emitJson($output, $normalized)
                ? Command::SUCCESS
                : Command::FAILURE;
        }

        $output->writeln(sprintf(
            'PAYOUT_CREATED id=%s vendorId=%s currency=%s grossCents=%d feeCents=%d netCents=%d status=%s',
            self::stringValue($normalized['id'] ?? null),
            self::stringValue($normalized['vendorId'] ?? null),
            self::stringValue($normalized['currency'] ?? null),
            self::intValue($normalized['grossCents'] ?? null),
            self::intValue($normalized['feeCents'] ?? null),
            self::intValue($normalized['netCents'] ?? null),
            self::stringValue($normalized['status'] ?? null),
        ));

        return Command::SUCCESS;
    }

    private static function stringValue(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    private static function intValue(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }

    private static function defaultCommandResultEmitter(): CommandResultEmitterInterface
    {
        return new CommandResultEmitter(new CommandJsonEncoder());
    }
}
