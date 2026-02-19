<?php
declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Command\Vendor;

use App\RepositoryInterface\Vendor\VendorApiKeyRepositoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:vendor:api-key:list',
    description: 'List vendor API keys',
)]
final class VendorApiKeyListCommand extends Command
{
    public function __construct(private readonly VendorApiKeyRepositoryInterface $apiKeyRepo)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('vendorId', null, InputOption::VALUE_REQUIRED, 'Vendor ID')
            ->setHelp('Example: php bin/console app:vendor:api-key:list --vendorId=123');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $vendorId = (int)$input->getOption('vendorId');

        if ($vendorId <= 0) {
            $output->writeln('<error>Invalid vendorId</error>');

            return Command::FAILURE;
        }

        $keys = $this->apiKeyRepo->findBy(['vendor' => $vendorId], ['createdAt' => 'DESC']);

        if ([] === $keys) {
            $output->writeln('<comment>No API keys found.</comment>');

            return Command::SUCCESS;
        }

        foreach ($keys as $key) {
            $output->writeln(sprintf(
                'ID: %d | Status: %s | Permissions: %s | Last Used: %s',
                $key->getId(),
                $key->getStatus(),
                $key->getPermissions(),
                $key->getLastUsedAt()?->format(DATE_ATOM) ?? 'never',
            ));
        }

        return Command::SUCCESS;
    }
}
