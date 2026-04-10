<?php

declare(strict_types=1);

namespace App\Command;

use App\ServiceInterface\Ops\ReleaseManifestBuilderInterface;
use App\ServiceInterface\Ops\RollbackDecisionEvaluatorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CLI entrypoint for rendering and optionally writing release/rollback manifests.
 */
#[AsCommand(name: 'app:vendor:release-manifest', description: 'Render release manifest and rollback decision')]
final class VendorReleaseManifestCommand extends Command
{
    public function __construct(
        private readonly ReleaseManifestBuilderInterface $releaseManifestBuilder,
        private readonly RollbackDecisionEvaluatorInterface $rollbackDecisionEvaluator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();
        $this
            ->addOption('windowSeconds', null, InputOption::VALUE_OPTIONAL, 'Lookback window in seconds', '900')
            ->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Output format: text|json', 'text')
            ->addOption('write', null, InputOption::VALUE_NONE, 'Write JSON manifests to build/release')
            ->addOption('manifest-output', null, InputOption::VALUE_OPTIONAL, 'Custom output path for release manifest')
            ->addOption('rollback-output', null, InputOption::VALUE_OPTIONAL, 'Custom output path for rollback manifest');
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $windowOption = $input->getOption('windowSeconds');
        $formatOption = $input->getOption('format');
        $windowSeconds = is_scalar($windowOption) ? max(1, (int) $windowOption) : 900;
        $format = is_scalar($formatOption) ? (string) $formatOption : 'text';

        $manifest = $this->releaseManifestBuilder->build($windowSeconds);
        $rollback = $this->rollbackDecisionEvaluator->evaluate($manifest);

        if ($input->getOption('write')) {
            $this->writeJson(
                is_scalar($input->getOption('manifest-output')) ? (string) $input->getOption('manifest-output') : dirname(__DIR__, 2).'/build/release/release-manifest.json',
                $manifest,
            );
            $this->writeJson(
                is_scalar($input->getOption('rollback-output')) ? (string) $input->getOption('rollback-output') : dirname(__DIR__, 2).'/build/release/rollback-manifest.json',
                $rollback,
            );
        }

        if ('json' === $format) {
            $output->writeln((string) json_encode(['manifest' => $manifest, 'rollback' => $rollback], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return Command::SUCCESS;
        }

        $output->writeln(sprintf(
            'release.status=%s monitoring.status=%s rollback.decision=%s severity=%s',
            $manifest['status'],
            $manifest['monitoring']['status'],
            $rollback['decision'],
            $rollback['severity'],
        ));
        $output->writeln(sprintf('alerts=%d reasons=%d', (int) $manifest['monitoring']['alertCount'], count($rollback['reasons'])));

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
