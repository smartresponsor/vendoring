<?php
declare(strict_types=1);

namespace App\CLI\Vendor;

use App\Repository\Vendor\VendorApiKeyRepository;
use App\Service\Vendor\VendorSecurityService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:vendor:api-key:rotate', description: 'Rotate API key')]
final class ApiKeyRotateCommand extends Command
{
    public function __construct(
        private readonly VendorApiKeyRepository $keys,
        private readonly VendorSecurityService $security
    ) { parent::__construct(); }

    protected function configure(): void
    {
        $this->addArgument('keyId', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = (int) $input->getArgument('keyId');
        $key = $this->keys->find($id);
        if (!$key) {
            $output->writeln('<error>Key not found</error>');
            return Command::FAILURE;
        }
        $plain = $this->security->rotateKey($key);
        $output->writeln('New API key (store securely): ' . $plain);
        return Command::SUCCESS;
    }
}
