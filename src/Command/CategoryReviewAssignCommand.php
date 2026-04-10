<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\CatalogReviewAssignmentService;
use InvalidArgumentException;
use JsonException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CLI entrypoint for assigning category review requests to a reviewer.
 *
 * This command is write-side: it delegates to the catalog review assignment service and renders the
 * resulting assignment payload as JSON. Invalid input is reported as console failure without
 * exposing an internal stack trace.
 */
#[AsCommand(name: 'app:category:review:assign', description: 'Assign a category change request for review')]
final class CategoryReviewAssignCommand extends Command
{
    public function __construct(private readonly CatalogReviewAssignmentService $service)
    {
        parent::__construct();
    }

    /**
     * Configure required CLI arguments and optional assignment priority.
     */
    protected function configure(): void
    {
        parent::configure();
        $this
            ->addArgument('requestId', InputArgument::REQUIRED)
            ->addArgument('reviewer', InputArgument::REQUIRED)
            ->addArgument('assignedBy', InputArgument::REQUIRED)
            ->addOption('priority', null, InputOption::VALUE_OPTIONAL, 'Assignment priority', 'medium');
    }

    /**
     * Assign the requested category review and render the resulting payload as JSON.
     *
     * @param InputInterface $input Console input carrying request, reviewer, assigner, and priority.
     * @param OutputInterface $output Console output that receives either JSON payload or a validation error.
     *
     * @return int `Command::SUCCESS` when the assignment is created, otherwise `Command::FAILURE` for
     *             invalid input detected by the underlying assignment service.
     * @throws JsonException
     */
    /** @noinspection PhpMissingParentCallCommonInspection */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $payload = $this->service->assign(
                self::stringArgument($input->getArgument('requestId')),
                self::stringArgument($input->getArgument('reviewer')),
                self::stringArgument($input->getArgument('assignedBy')),
                is_scalar($input->getOption('priority')) ? (string) $input->getOption('priority') : null,
            );
        } catch (InvalidArgumentException $exception) {
            $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));

            return Command::FAILURE;
        }

        $output->writeln((string) json_encode($payload, JSON_THROW_ON_ERROR));

        return Command::SUCCESS;
    }

    /**
     * Normalize a console argument into a scalar string representation.
     *
     * @param mixed $value Raw console argument value.
     *
     * @return string Scalar string value or an empty string when the raw value is not scalar.
     */
    private static function stringArgument(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }
}
