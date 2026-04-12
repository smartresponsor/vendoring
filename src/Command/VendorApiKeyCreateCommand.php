<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Command;

use App\Command\Support\CommandOutputFormat;
use App\Command\Support\CommandResultEmitter;
use App\Command\Support\CommandResultEmitterInterface;
use App\RepositoryInterface\VendorRepositoryInterface;
use App\ServiceInterface\VendorApiKeyServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

#[AsCommand(
    name: 'app:vendor:api-key:create',
    description: 'Create a new API key for a vendor',
)]
final class VendorApiKeyCreateCommand extends Command
{
    public function __construct(
        private readonly VendorRepositoryInterface $vendorRepo,
        private readonly VendorApiKeyServiceInterface $apiKeyService,
        private readonly CommandResultEmitterInterface $commandResultEmitter,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();
        $this
            ->addOption('vendorId', null, InputOption::VALUE_REQUIRED, 'Vendor ID')
            ->addOption('permissions', null, InputOption::VALUE_OPTIONAL, 'Permissions CSV', 'read')
            ->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Output format: text|json', 'text');
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $vendorIdOption = $input->getOption('vendorId');
        $permissionsOption = $input->getOption('permissions');
        $formatOption = $input->getOption('format');

        $vendorId = is_scalar($vendorIdOption) ? (int) (string) $vendorIdOption : 0;
        $permissions = is_scalar($permissionsOption) ? (string) $permissionsOption : 'read';
        $format = CommandOutputFormat::normalize($formatOption);

        if ($vendorId <= 0) {
            $this->commandResultEmitter->emitError($output, $format, 'invalid', 'Invalid vendorId', [
                'vendorId' => $vendorId,
            ]);

            return Command::FAILURE;
        }

        try {
            $vendor = $this->vendorRepo->find($vendorId);
        } catch (Throwable $throwable) {
            $this->commandResultEmitter->emitThrowableError($output, $format, 'failed', 'Failed to load vendor', $throwable, [
                'vendorId' => $vendorId,
            ]);

            return Command::FAILURE;
        }

        if (null === $vendor) {
            $this->commandResultEmitter->emitError($output, $format, 'not_found', 'Vendor not found', [
                'vendorId' => $vendorId,
            ]);

            return Command::FAILURE;
        }

        try {
            $token = $this->apiKeyService->createKey($vendor, $permissions);
        } catch (Throwable $throwable) {
            $this->commandResultEmitter->emitThrowableError($output, $format, 'failed', 'Failed to create API key', $throwable, [
                'vendorId' => $vendorId,
                'permissions' => $permissions,
            ]);

            return Command::FAILURE;
        }

        if (CommandOutputFormat::isJson($format)) {
            if (!$this->commandResultEmitter->emitJson($output, [
                'vendorId' => $vendorId,
                'permissions' => $permissions,
                'token' => $token,
                'status' => 'created',
            ])) {
                return Command::FAILURE;
            }

            return Command::SUCCESS;
        }

        $output->writeln('<info>API key created successfully</info>');
        $output->writeln(sprintf(
            'vendorId=%d permissions=%s token=%s',
            $vendorId,
            $permissions,
            $token,
        ));

        return Command::SUCCESS;
    }
}
