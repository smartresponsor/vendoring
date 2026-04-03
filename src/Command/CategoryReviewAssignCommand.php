<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\CatalogReviewAssignmentService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:category:review:assign', description: 'Assign a category change request for review')]
final class CategoryReviewAssignCommand extends Command
{
    public function __construct(private readonly CatalogReviewAssignmentService $service)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('requestId', InputArgument::REQUIRED)
            ->addArgument('reviewer', InputArgument::REQUIRED)
            ->addArgument('assignedBy', InputArgument::REQUIRED)
            ->addOption('priority', null, InputOption::VALUE_OPTIONAL, 'Assignment priority', 'medium');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $payload = $this->service->assign(
                self::stringArgument($input->getArgument('requestId')),
                self::stringArgument($input->getArgument('reviewer')),
                self::stringArgument($input->getArgument('assignedBy')),
                is_scalar($input->getOption('priority')) ? (string) $input->getOption('priority') : null,
            );
        } catch (\InvalidArgumentException $exception) {
            $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));

            return Command::FAILURE;
        }

        $output->writeln((string) json_encode($payload, JSON_THROW_ON_ERROR));

        return Command::SUCCESS;
    }

    private static function stringArgument(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }
}
