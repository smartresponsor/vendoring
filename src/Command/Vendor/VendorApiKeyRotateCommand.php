<?php
declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Command\Vendor;

use App\RepositoryInterface\Vendor\VendorApiKeyRepositoryInterface;
use App\ServiceInterface\Vendor\VendorSecurityServiceInterface;
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
        private readonly VendorSecurityServiceInterface  $securityService,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('keyId', null, InputOption::VALUE_REQUIRED, 'API key ID')
            ->setHelp('Example: php bin/console app:vendor:api-key:rotate --keyId=555');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $keyId = (int)$input->getOption('keyId');

        if ($keyId <= 0) {
            $output->writeln('<error>Invalid keyId</error>');

            return Command::FAILURE;
        }

        $key = $this->apiKeyRepo->find($keyId);

        if (null === $key) {
            $output->writeln('<error>API key not found</error>');

            return Command::FAILURE;
        }

        $newToken = $this->securityService->rotateKey($key);

        $output->writeln('<info>API key rotated successfully</info>');
        $output->writeln('New token: ' . $newToken);

        return Command::SUCCESS;
    }
}
