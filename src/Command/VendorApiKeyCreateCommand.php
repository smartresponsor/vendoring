<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Command;

use App\RepositoryInterface\VendorRepositoryInterface;
use App\ServiceInterface\VendorApiKeyServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:vendor:api-key:create',
    description: 'Create an API key for a vendor',
)]
final class VendorApiKeyCreateCommand extends Command
{
    public function __construct(
        private readonly VendorRepositoryInterface $vendorRepo,
        private readonly VendorApiKeyServiceInterface $apiKeyService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();
        $this
            ->addOption('vendorId', null, InputOption::VALUE_REQUIRED, 'Vendor ID')
            ->addOption('permissions', null, InputOption::VALUE_OPTIONAL, 'Permissions (comma-separated)', 'read')
            ->setHelp('Usage: php bin/console app:vendor:api-key:create --vendorId=123 --permissions=read,write');
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $vendorIdOption = $input->getOption('vendorId');
        $permissionsOption = $input->getOption('permissions');
        $vendorId = is_scalar($vendorIdOption) ? (int) (string) $vendorIdOption : 0;
        $permissions = is_scalar($permissionsOption) ? (string) $permissionsOption : 'read';

        if ($vendorId <= 0) {
            $output->writeln('<error>Invalid vendorId</error>');

            return Command::FAILURE;
        }

        $vendor = $this->vendorRepo->find($vendorId);

        if (null === $vendor) {
            $output->writeln('<error>Vendor not found</error>');

            return Command::FAILURE;
        }

        $token = $this->apiKeyService->createKey($vendor, $permissions);

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
