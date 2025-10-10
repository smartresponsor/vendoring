<?php
declare(strict_types=1);

namespace App\CLI\Vendor;

use App\Repository\Vendor\VendorRepository;
use App\Service\Vendor\VendorSecurityService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:vendor:api-key:create', description: 'Create API key for Vendor')]
final class ApiKeyCreateCommand extends Command
{
    public function __construct(
        private readonly VendorRepository $vendors,
        private readonly VendorSecurityService $security
    ) { parent::__construct(); }

    protected function configure(): void
    {
        $this->addArgument('vendorId', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $vendorId = (int) $input->getArgument('vendorId');
        $vendor = $this->vendors->find($vendorId);
        if (!$vendor) {
            $output->writeln('<error>Vendor not found</error>');
            return Command::FAILURE;
        }
        $res = $this->security->createKey($vendor, ['vendor:read','vendor:write']);
        $output->writeln('API key (store securely): ' . $res['plain']);
        return Command::SUCCESS;
    }
}
