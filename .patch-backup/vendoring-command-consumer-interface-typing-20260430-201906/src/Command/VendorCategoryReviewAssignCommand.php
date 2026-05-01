<?php

declare(strict_types=1);

namespace App\Vendoring\Command;

use App\Vendoring\Service\Command\VendorCommandJsonEncoderService;
use App\Vendoring\Enum\Command\VendorCommandOutputFormatEnum;
use App\Vendoring\Service\Command\VendorCommandResultEmitterService;
use App\Vendoring\ServiceInterface\Command\VendorCommandResultEmitterServiceInterface;
use App\Vendoring\ServiceInterface\Catalog\VendorCatalogReviewAssignmentServiceInterface;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * CLI entrypoint for assigning category review requests to a reviewer.
 *
 * This command is write-side: it delegates to the catalog review assignment service and renders the
 * resulting assignment payload as JSON. Invalid input is reported as console failure without
 * exposing an internal stack trace.
 */
#[AsCommand(name: 'app:category:review:assign', description: 'Assign a category change request for review')]
final class VendorCategoryReviewAssignCommand extends Command
{
    private readonly VendorCatalogReviewAssignmentServiceInterface $service;
    private readonly VendorCommandResultEmitterServiceInterface $commandResultEmitter;

    public function __construct(
        VendorCatalogReviewAssignmentServiceInterface $service,
        ?VendorCommandResultEmitterServiceInterface $commandResultEmitter = null,
    ) {
        $this->service = $service;
        $this->commandResultEmitter = $commandResultEmitter ?? self::defaultVendorCommandResultEmitterService();
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
            ->addOption('priority', null, InputOption::VALUE_OPTIONAL, 'Assignment priority', 'medium')
            ->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Output format: text|json', 'json');
    }

    /**
     * Assign the requested category review and render the resulting payload as JSON.
     *
     * @param InputInterface $input Console input carrying request, reviewer, assigner, and priority.
     * @param OutputInterface $output Console output that receives either JSON payload or a validation error.
     *
     * @return int `Command::SUCCESS` when the assignment is created, otherwise `Command::FAILURE` for
     *             invalid input detected by the underlying assignment service.
     */
    /** @noinspection PhpMissingParentCallCommonInspection */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $format = VendorCommandOutputFormatEnum::normalize($input->getOption('format'));
        $requestId = self::stringArgument($input->getArgument('requestId'));
        $reviewer = self::stringArgument($input->getArgument('reviewer'));
        $assignedBy = self::stringArgument($input->getArgument('assignedBy'));
        $priority = is_scalar($input->getOption('priority')) ? (string) $input->getOption('priority') : null;

        try {
            $payload = $this->service->assign($requestId, $reviewer, $assignedBy, $priority);
        } catch (InvalidArgumentException $exception) {
            $this->commandResultEmitter->emitError($output, $format, 'invalid', $exception->getMessage(), [
                'requestId' => $requestId,
                'reviewer' => $reviewer,
                'assignedBy' => $assignedBy,
                'priority' => $priority,
            ]);

            return Command::FAILURE;
        } catch (Throwable $throwable) {
            $this->commandResultEmitter->emitThrowableError($output, $format, 'failed', 'Failed to assign category review', $throwable, [
                'requestId' => $requestId,
                'reviewer' => $reviewer,
                'assignedBy' => $assignedBy,
                'priority' => $priority,
            ]);

            return Command::FAILURE;
        }

        if (VendorCommandOutputFormatEnum::isJson($format)) {
            return $this->commandResultEmitter->emitJson($output, $payload)
                ? Command::SUCCESS
                : Command::FAILURE;
        }

        $output->writeln(sprintf(
            'requestId=%s reviewer=%s assignedBy=%s priority=%s status=%s',
            self::stringArgument($payload['requestId'] ?? null),
            self::stringArgument($payload['reviewer'] ?? null),
            self::stringArgument($payload['assignedBy'] ?? null),
            self::stringArgument($payload['priority'] ?? null),
            self::stringArgument($payload['status'] ?? null),
        ));

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

    private static function defaultVendorCommandResultEmitterService(): VendorCommandResultEmitterServiceInterface
    {
        return new VendorCommandResultEmitterService(new VendorCommandJsonEncoderService());
    }
}
