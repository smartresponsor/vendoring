<?php

declare(strict_types=1);

namespace App\Command;

use App\Command\Support\CommandIoException;
use App\Command\Support\CommandJsonArtifactWriter;
use App\Command\Support\CommandOutputFormat;
use App\Command\Support\CommandResultEmitter;
use App\ServiceInterface\Observability\AlertRuleEvaluatorInterface;
use App\ServiceInterface\Observability\MonitoringSnapshotBuilderInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * CLI entrypoint for rendering the current monitoring snapshot and alerts.
 */
#[AsCommand(name: 'app:vendor:monitoring-snapshot', description: 'Render monitoring snapshot and alerts')]
final class VendorMonitoringSnapshotCommand extends Command
{
    public function __construct(
        private readonly MonitoringSnapshotBuilderInterface $snapshotBuilder,
        private readonly AlertRuleEvaluatorInterface $alertRuleEvaluator,
        private readonly CommandJsonArtifactWriter $commandJsonArtifactWriter,
        private readonly CommandResultEmitter $commandResultEmitter,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();
        $this
            ->addOption('windowSeconds', null, InputOption::VALUE_OPTIONAL, 'Lookback window in seconds', '900')
            ->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Output format: text|json', 'text')
            ->addOption('write', null, InputOption::VALUE_NONE, 'Write monitoring snapshot JSON to build/release/monitoring-snapshot.json')
            ->addOption('output', null, InputOption::VALUE_OPTIONAL, 'Custom monitoring snapshot output path');
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $windowOption = $input->getOption('windowSeconds');
        $formatOption = $input->getOption('format');
        $windowSeconds = is_scalar($windowOption) ? max(1, (int) $windowOption) : 900;
        $format = CommandOutputFormat::normalize($formatOption);

        try {
            $snapshot = $this->snapshotBuilder->build($windowSeconds);
            $alerts = $this->alertRuleEvaluator->evaluate($snapshot);
        } catch (Throwable $throwable) {
            $this->commandResultEmitter->emitThrowableError(
                $output,
                $format,
                'failed',
                'Failed to build monitoring snapshot',
                $throwable,
                ['windowSeconds' => $windowSeconds],
            );

            return Command::FAILURE;
        }

        $payload = [
            'snapshot' => $snapshot,
            'alerts' => $alerts,
        ];

        try {
            $writtenPath = $this->commandJsonArtifactWriter->writeIfRequested(
                (bool) $input->getOption('write'),
                $input->getOption('output'),
                dirname(__DIR__, 2) . '/build/release/monitoring-snapshot.json',
                $payload,
            );
        } catch (CommandIoException $exception) {
            $this->commandResultEmitter->emitError($output, $format, 'failed', $exception->getMessage(), [
                'windowSeconds' => $windowSeconds,
            ]);

            return Command::FAILURE;
        }

        if (CommandOutputFormat::isJson($format)) {
            return $this->commandResultEmitter->emitJson($output, $payload)
                ? Command::SUCCESS
                : Command::FAILURE;
        }

        $output->writeln(sprintf('status=%s windowSeconds=%d', $snapshot['status'], $windowSeconds));
        $output->writeln(sprintf(
            'logs.total=%d logs.error=%d metrics.total=%d breakers.open=%d',
            (int) $snapshot['logSummary']['total'],
            (int) $snapshot['logSummary']['error'],
            (int) $snapshot['metricSummary']['total'],
            (int) $snapshot['breakerSummary']['open'],
        ));
        $output->writeln(sprintf('alerts=%d', count($alerts)));

        if (null !== $writtenPath) {
            $output->writeln(sprintf('written=%s', $writtenPath));
        }

        return Command::SUCCESS;
    }
}
