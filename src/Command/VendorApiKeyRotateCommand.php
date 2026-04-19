<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\Command;

use App\Vendoring\Command\Support\CommandOutputFormat;
use App\Vendoring\Command\Support\CommandResultEmitterInterface;
use App\Vendoring\RepositoryInterface\VendorApiKeyRepositoryInterface;
use App\Vendoring\ServiceInterface\VendorApiKeyServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

#[AsCommand(
    name: 'app:vendor:api-key:rotate',
    description: 'Rotate an API key',
)]
final class VendorApiKeyRotateCommand extends Command
{
    public function __construct(
        private readonly VendorApiKeyRepositoryInterface $apiKeyRepo,
        private readonly VendorApiKeyServiceInterface $apiKeyService,
        private readonly CommandResultEmitterInterface $commandResultEmitter,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();
        $this
            ->addOption('keyId', null, InputOption::VALUE_REQUIRED, 'API key ID')
            ->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Output format: text|json', 'text')
            ->setHelp('Usage: php bin/console app:vendor:api-key:rotate --keyId=555');
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $keyIdOption = $input->getOption('keyId');
        $formatOption = $input->getOption('format');
        $keyId = is_scalar($keyIdOption) ? (int) (string) $keyIdOption : 0;
        $format = CommandOutputFormat::normalize($formatOption);

        if ($keyId <= 0) {
            $this->commandResultEmitter->emitError($output, $format, 'invalid', 'Invalid keyId', [
                'keyId' => $keyId,
            ]);

            return Command::FAILURE;
        }

        try {
            $key = $this->apiKeyRepo->find($keyId);
        } catch (Throwable $throwable) {
            $this->commandResultEmitter->emitThrowableError($output, $format, 'failed', 'Failed to load API key', $throwable, [
                'keyId' => $keyId,
            ]);

            return Command::FAILURE;
        }

        if (null === $key) {
            $this->commandResultEmitter->emitError($output, $format, 'not_found', 'API key not found', [
                'keyId' => $keyId,
            ]);

            return Command::FAILURE;
        }

        try {
            $newToken = $this->apiKeyService->rotateKey($key);
        } catch (Throwable $throwable) {
            $this->commandResultEmitter->emitThrowableError($output, $format, 'failed', 'Failed to rotate API key', $throwable, [
                'keyId' => $keyId,
            ]);

            return Command::FAILURE;
        }

        if (CommandOutputFormat::isJson($format)) {
            if (!$this->commandResultEmitter->emitJson($output, [
                'keyId' => $keyId,
                'status' => $key->getStatus(),
                'permissions' => $key->getPermissions(),
                'token' => $newToken,
            ])) {
                return Command::FAILURE;
            }

            return Command::SUCCESS;
        }

        $output->writeln('<info>API key rotated successfully</info>');
        $output->writeln(sprintf(
            'keyId=%d status=%s permissions=%s token=%s',
            $keyId,
            $key->getStatus(),
            $key->getPermissions(),
            $newToken,
        ));

        return Command::SUCCESS;
    }
}
