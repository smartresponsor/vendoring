<?php

declare(strict_types=1);

namespace App\Command;

use App\ServiceInterface\Observability\AlertRuleEvaluatorInterface;
use App\ServiceInterface\Observability\MonitoringSnapshotBuilderInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CLI entrypoint for rendering the current monitoring snapshot and alerts.
 */
#[AsCommand(name: 'app:vendor:monitoring-snapshot', description: 'Render monitoring snapshot and alerts')]
final class VendorMonitoringSnapshotCommand extends Command
{
    public function __construct(
        private readonly MonitoringSnapshotBuilderInterface $snapshotBuilder,
        private readonly AlertRuleEvaluatorInterface $alertRuleEvaluator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('windowSeconds', null, InputOption::VALUE_OPTIONAL, 'Lookback window in seconds', '900')
            ->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Output format: text|json', 'text');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $windowOption = $input->getOption('windowSeconds');
        $formatOption = $input->getOption('format');
        $windowSeconds = is_scalar($windowOption) ? max(1, (int) $windowOption) : 900;
        $format = is_scalar($formatOption) ? (string) $formatOption : 'text';

        $snapshot = $this->snapshotBuilder->build($windowSeconds);
        $alerts = $this->alertRuleEvaluator->evaluate($snapshot);

        if ('json' === $format) {
            $output->writeln((string) json_encode(['snapshot' => $snapshot, 'alerts' => $alerts], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return Command::SUCCESS;
        }

        $output->writeln(sprintf('status=%s windowSeconds=%d', (string) $snapshot['status'], $windowSeconds));
        $output->writeln(sprintf(
            'logs.total=%d logs.error=%d metrics.total=%d breakers.open=%d',
            (int) $snapshot['logSummary']['total'],
            (int) $snapshot['logSummary']['error'],
            (int) $snapshot['metricSummary']['total'],
            (int) $snapshot['breakerSummary']['open'],
        ));
        $output->writeln(sprintf('alerts=%d', count($alerts)));

        return Command::SUCCESS;
    }
}
