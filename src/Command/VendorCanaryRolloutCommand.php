<?php

declare(strict_types=1);

namespace App\Command;

use App\ServiceInterface\Rollout\CanaryRolloutCoordinatorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CLI entrypoint for rendering canary rollout readiness and recommended next action.
 */
#[AsCommand(name: 'app:vendor:canary-rollout', description: 'Render canary rollout readiness for one flag and cohort')]
final class VendorCanaryRolloutCommand extends Command
{
    public function __construct(private readonly CanaryRolloutCoordinatorInterface $canaryRolloutCoordinator)
    {
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $flagOption = $input->getOption('flag');
        $flagName = is_scalar($flagOption) ? trim((string) $flagOption) : '';
        if ('' === $flagName) {
            $output->writeln('flag option is required');

            return Command::INVALID;
        }

        $tenantOption = $input->getOption('tenantId');
        $vendorOption = $input->getOption('vendorId');
        $windowOption = $input->getOption('windowSeconds');
        $formatOption = $input->getOption('format');

        $tenantId = is_scalar($tenantOption) && '' !== trim((string) $tenantOption) ? trim((string) $tenantOption) : null;
        $vendorId = is_scalar($vendorOption) && '' !== trim((string) $vendorOption) ? trim((string) $vendorOption) : null;
        $windowSeconds = is_scalar($windowOption) ? max(1, (int) $windowOption) : 900;
        $format = is_scalar($formatOption) ? (string) $formatOption : 'text';

        $report = $this->canaryRolloutCoordinator->evaluate($flagName, $tenantId, $vendorId, $windowSeconds);

        if ($input->getOption('write')) {
            $path = is_scalar($input->getOption('output')) && '' !== trim((string) $input->getOption('output'))
                ? trim((string) $input->getOption('output'))
                : dirname(__DIR__, 2).'/build/release/canary-rollout.json';
            $this->writeJson($path, $report);
        }

        if ('json' === $format) {
            $output->writeln((string) json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return Command::SUCCESS;
        }

        $output->writeln(sprintf(
            'flag=%s cohort=%s decision=%s action=%s next=%s',
            $report['flagDecision']['flag'],
            $report['canary']['cohort'],
            $report['canary']['decision'],
            $report['canary']['recommendedAction'],
            $report['canary']['nextCohort'] ?? 'none',
        ));

        return Command::SUCCESS;
    }

    /**
     * @param array<string,mixed> $payload
     */
    private function writeJson(string $path, array $payload): void
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($path, (string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
