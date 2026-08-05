<?php

declare(strict_types=1);

namespace App\Vendoring\Command;

use App\Vendoring\DataFixtures\VendorAccessBootstrapFixture;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:vendor:fixtures:load',
    description: 'Creates Objecting-backed Vendor fixtures for existing Access users.',
)]
final class VendorFixtureLoadCommand extends Command
{
    public function __construct(
        private readonly VendorAccessBootstrapFixture $fixture,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->fixture->load($this->entityManager);

        $output->writeln('<info>Vendor fixtures loaded.</info>');

        return Command::SUCCESS;
    }
}
