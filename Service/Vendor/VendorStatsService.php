<?php
declare(strict_types=1);

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:vendor:stats', description: 'Show Vendor event statistics from audit trail')]
final class VendorStatsCommand extends Command
{
    public function __construct(private readonly Connection $db)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rows = $this->db->fetchAllAssociative("SELECT action, COUNT(*) AS cnt FROM audit_trail GROUP BY action ORDER BY cnt DESC");
        foreach ($rows as $r) {
            $output->writeln(sprintf("%-40s %d", $r['action'], $r['cnt']));
        }
        return Command::SUCCESS;
    }
}
