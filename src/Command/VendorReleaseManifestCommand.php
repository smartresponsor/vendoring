<?php

declare(strict_types=1);

namespace App\Command;

use App\Command\Support\CommandIoException;
use App\Command\Support\CommandJsonArtifactWriter;
use App\Command\Support\CommandOutputFormat;
use App\Command\Support\CommandResultEmitter;
use App\ServiceInterface\Ops\ReleaseManifestBuilderInterface;
use App\ServiceInterface\Ops\RollbackDecisionEvaluatorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * CLI entrypoint for rendering and optionally writing release/rollback manifests.
 */
#[AsCommand(name: 'app:vendor:release-manifest', description: 'Render release manifest and rollback decision')]
final class VendorReleaseManifestCommand extends Command
{
    public function __construct(
        private readonly ReleaseManifestBuilderInterface $releaseManifestBuilder,
        private readonly RollbackDecisionEvaluatorInterface $rollbackDecisionEvaluator,
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
        $format = CommandOutputFormat::normalize($formatOption);

        try {
            $manifest = $this->releaseManifestBuilder->build($windowSeconds);
            $rollback = $this->rollbackDecisionEvaluator->evaluate($manifest);
        } catch (Throwable $throwable) {
            $this->commandResultEmitter->emitThrowableError(
                $output,
                $format,
                'failed',
                'Failed to build release manifest',
                $throwable,
                ['windowSeconds' => $windowSeconds],
            );

            return Command::FAILURE;
        }

        try {
            $manifestPath = $this->commandJsonArtifactWriter->writeIfRequested(
                (bool) $input->getOption('write'),
                $input->getOption('manifest-output'),
                dirname(__DIR__, 2) . '/build/release/release-manifest.json',
                $manifest,
            );
            $rollbackPath = $this->commandJsonArtifactWriter->writeIfRequested(
                (bool) $input->getOption('write'),
                $input->getOption('rollback-output'),
                dirname(__DIR__, 2) . '/build/release/rollback-manifest.json',
                $rollback,
            );
        } catch (CommandIoException $exception) {
            $this->commandResultEmitter->emitError($output, $format, 'failed', $exception->getMessage(), [
                'windowSeconds' => $windowSeconds,
            ]);

            return Command::FAILURE;
        }

        if (CommandOutputFormat::isJson($format)) {
            return $this->commandResultEmitter->emitJson($output, [
                'manifest' => $manifest,
                'rollback' => $rollback,
            ]) ? Command::SUCCESS : Command::FAILURE;
        }

        $output->writeln(sprintf(
            'release.status=%s monitoring.status=%s rollback.decision=%s severity=%s',
            $manifest['status'],
            $manifest['monitoring']['status'],
            $rollback['decision'],
            $rollback['severity'],
        ));
        $output->writeln(sprintf('alerts=%d reasons=%d', (int) $manifest['monitoring']['alertCount'], count($rollback['reasons'])));

        if (null !== $manifestPath) {
            $output->writeln(sprintf('manifestWritten=%s', $manifestPath));
        }

        if (null !== $rollbackPath) {
            $output->writeln(sprintf('rollbackWritten=%s', $rollbackPath));
        }

        return Command::SUCCESS;
    }
}
