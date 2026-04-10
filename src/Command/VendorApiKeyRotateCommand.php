<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Command;

use App\RepositoryInterface\VendorApiKeyRepositoryInterface;
use App\ServiceInterface\VendorApiKeyServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:vendor:api-key:rotate',
    description: 'Rotate an API key',
)]
final class VendorApiKeyRotateCommand extends Command
{
    public function __construct(
        private readonly VendorApiKeyRepositoryInterface $apiKeyRepo,
        private readonly VendorApiKeyServiceInterface $apiKeyService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();
        $this
            ->addOption('keyId', null, InputOption::VALUE_REQUIRED, 'API key ID')
            ->setHelp('Usage: php bin/console app:vendor:api-key:rotate --keyId=555');
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $keyIdOption = $input->getOption('keyId');
        $keyId = is_scalar($keyIdOption) ? (int) (string) $keyIdOption : 0;

        if ($keyId <= 0) {
            $output->writeln('<error>Invalid keyId</error>');

            return Command::FAILURE;
        }

        $key = $this->apiKeyRepo->find($keyId);

        if (null === $key) {
            $output->writeln('<error>API key not found</error>');

            return Command::FAILURE;
        }

        $newToken = $this->apiKeyService->rotateKey($key);

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
