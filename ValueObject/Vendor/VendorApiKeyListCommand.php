<?php
declare(strict_types=1);

namespace App\CLI\Vendor;

use App\Repository\Vendor\VendorRepository;
use App\Repository\Vendor\VendorApiKeyRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:vendor:api-key:list', description: 'List Vendor API keys')]
final class ApiKeyListCommand extends Command
{
    public function __construct(
        private readonly VendorRepository $vendors,
        private readonly VendorApiKeyRepository $keys
    ) { parent::__construct(); }

    protected function configure(): void
    {
        self::setHelp('Lists keys by vendor id');
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
        $rows = $this->keys->findBy(['vendor' => $vendor]);
        foreach ($rows as $k) {
            $output->writeln(sprintf('#%d  %s  perms=%s  status=%s  lastUsed=%s',
                $k->getId(), '***', json_encode($k->getPermissions()), $k->getStatus(), $k->getLastUsedAt()?->format(DATE_ATOM) ?? '-'
            ));
        }
        return Command::SUCCESS;
    }
}
