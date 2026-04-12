<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Command;

use App\Command\Support\CommandOutputFormat;
use App\Command\Support\CommandResultEmitter;
use App\Command\Support\CommandResultEmitterInterface;
use App\RepositoryInterface\VendorApiKeyRepositoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

#[AsCommand(
    name: 'app:vendor:api-key:list',
    description: 'List vendor API keys',
)]
final class VendorApiKeyListCommand extends Command
{
    public function __construct(
        private readonly VendorApiKeyRepositoryInterface $apiKeyRepo,
        private readonly CommandResultEmitterInterface $commandResultEmitter,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();
        $this
            ->addOption('vendorId', null, InputOption::VALUE_REQUIRED, 'Vendor ID')
            ->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Output format: text|json', 'text')
            ->setHelp('Usage: php bin/console app:vendor:api-key:list --vendorId=123');
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $vendorIdOption = $input->getOption('vendorId');
        $formatOption = $input->getOption('format');
        $vendorId = is_scalar($vendorIdOption) ? (int) (string) $vendorIdOption : 0;
        $format = CommandOutputFormat::normalize($formatOption);

        if ($vendorId <= 0) {
            $this->commandResultEmitter->emitError($output, $format, 'invalid', 'Invalid vendorId', [
                'vendorId' => $vendorId,
            ]);

            return Command::FAILURE;
        }

        try {
            $keys = $this->apiKeyRepo->findBy(['vendor' => $vendorId], ['createdAt' => 'DESC']);
        } catch (Throwable $throwable) {
            $this->commandResultEmitter->emitThrowableError($output, $format, 'failed', 'Failed to load API keys', $throwable, [
                'vendorId' => $vendorId,
            ]);

            return Command::FAILURE;
        }

        if (CommandOutputFormat::isJson($format)) {
            if (!$this->commandResultEmitter->emitJson($output, [
                'vendorId' => $vendorId,
                'total' => count($keys),
                'keys' => array_map(
                    static fn($key): array => [
                        'keyId' => $key->getId(),
                        'status' => $key->getStatus(),
                        'permissions' => $key->getPermissions(),
                        'lastUsedAt' => $key->getLastUsedAt()?->format(DATE_ATOM) ?? 'never',
                    ],
                    $keys,
                ),
            ])) {
                return Command::FAILURE;
            }

            return Command::SUCCESS;
        }

        if ([] === $keys) {
            $output->writeln(sprintf(
                '<comment>vendorId=%d total=0 keys=[]</comment>',
                $vendorId,
            ));

            return Command::SUCCESS;
        }

        $output->writeln(sprintf(
            '<info>vendorId=%d total=%d</info>',
            $vendorId,
            count($keys),
        ));

        foreach ($keys as $key) {
            $output->writeln(sprintf(
                'vendorId=%d keyId=%d status=%s permissions=%s lastUsedAt=%s',
                $vendorId,
                $key->getId(),
                $key->getStatus(),
                $key->getPermissions(),
                $key->getLastUsedAt()?->format(DATE_ATOM) ?? 'never',
            ));
        }

        return Command::SUCCESS;
    }
}
