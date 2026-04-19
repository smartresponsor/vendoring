<?php

declare(strict_types=1);

namespace App\Vendoring\Command;

use App\Vendoring\Command\Support\CommandIoException;
use App\Vendoring\Command\Support\CommandJsonArtifactWriter;
use App\Vendoring\Command\Support\CommandOutputFormat;
use App\Vendoring\Command\Support\CommandResultEmitter;
use App\Vendoring\ServiceInterface\Rollout\CanaryRolloutCoordinatorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * CLI entrypoint for rendering canary rollout readiness and recommended next action.
 */
#[AsCommand(name: 'app:vendor:canary-rollout', description: 'Render canary rollout readiness for one flag and cohort')]
final class VendorCanaryRolloutCommand extends Command
{
    public function __construct(
        private readonly CanaryRolloutCoordinatorInterface $canaryRolloutCoordinator,
        private readonly CommandJsonArtifactWriter $commandJsonArtifactWriter,
        private readonly CommandResultEmitter $commandResultEmitter,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();
        $this
            ->addOption('flag', null, InputOption::VALUE_REQUIRED, 'Canonical feature flag identifier')
            ->addOption('tenantId', null, InputOption::VALUE_OPTIONAL, 'Optional tenant scope')
            ->addOption('vendorId', null, InputOption::VALUE_OPTIONAL, 'Optional vendor scope')
            ->addOption('windowSeconds', null, InputOption::VALUE_OPTIONAL, 'Lookback window in seconds', '900')
            ->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Output format: text|json', 'text')
            ->addOption('write', null, InputOption::VALUE_NONE, 'Write canary rollout report to build/release/canary-rollout.json')
            ->addOption('output', null, InputOption::VALUE_OPTIONAL, 'Custom canary rollout output path');
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $flagOption = $input->getOption('flag');
        $flagName = is_scalar($flagOption) ? trim((string) $flagOption) : '';
        $format = CommandOutputFormat::normalize($input->getOption('format'));

        if ('' === $flagName) {
            $this->commandResultEmitter->emitError($output, $format, 'invalid', 'flag option is required');

            return Command::INVALID;
        }

        $tenantOption = $input->getOption('tenantId');
        $vendorOption = $input->getOption('vendorId');
        $windowOption = $input->getOption('windowSeconds');

        $tenantId = is_scalar($tenantOption) && '' !== trim((string) $tenantOption) ? trim((string) $tenantOption) : null;
        $vendorId = is_scalar($vendorOption) && '' !== trim((string) $vendorOption) ? trim((string) $vendorOption) : null;
        $windowSeconds = is_scalar($windowOption) ? max(1, (int) $windowOption) : 900;

        try {
            $report = $this->canaryRolloutCoordinator->evaluate($flagName, $tenantId, $vendorId, $windowSeconds);
        } catch (Throwable $throwable) {
            $this->commandResultEmitter->emitThrowableError(
                $output,
                $format,
                'failed',
                'Failed to evaluate canary rollout',
                $throwable,
                [
                    'flag' => $flagName,
                    'tenantId' => $tenantId,
                    'vendorId' => $vendorId,
                    'windowSeconds' => $windowSeconds,
                ],
            );

            return Command::FAILURE;
        }

        try {
            $writtenPath = $this->commandJsonArtifactWriter->writeIfRequested(
                (bool) $input->getOption('write'),
                $input->getOption('output'),
                dirname(__DIR__, 2) . '/build/release/canary-rollout.json',
                $report,
            );
        } catch (CommandIoException $exception) {
            $this->commandResultEmitter->emitError($output, $format, 'failed', $exception->getMessage(), [
                'flag' => $flagName,
                'tenantId' => $tenantId,
                'vendorId' => $vendorId,
                'windowSeconds' => $windowSeconds,
            ]);

            return Command::FAILURE;
        }

        if (CommandOutputFormat::isJson($format)) {
            return $this->commandResultEmitter->emitJson($output, $report)
                ? Command::SUCCESS
                : Command::FAILURE;
        }

        $output->writeln(sprintf(
            'flag=%s cohort=%s decision=%s action=%s next=%s',
            $report['flagDecision']['flag'],
            $report['canary']['cohort'],
            $report['canary']['decision'],
            $report['canary']['recommendedAction'],
            $report['canary']['nextCohort'] ?? 'none',
        ));

        if (null !== $writtenPath) {
            $output->writeln(sprintf('written=%s', $writtenPath));
        }

        return Command::SUCCESS;
    }
}
