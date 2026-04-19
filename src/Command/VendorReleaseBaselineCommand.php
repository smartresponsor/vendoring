<?php

declare(strict_types=1);

namespace App\Vendoring\Command;

use App\Vendoring\Command\Support\CommandIoException;
use App\Vendoring\Command\Support\CommandJsonArtifactWriter;
use App\Vendoring\Command\Support\CommandOutputFormat;
use App\Vendoring\Command\Support\CommandResultEmitter;
use App\Vendoring\Command\Support\VendorRuntimeWindowInput;
use App\Vendoring\ServiceInterface\Ops\VendorReleaseBaselineReaderInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

#[AsCommand(
    name: 'app:vendor:release-baseline',
    description: 'Render a release-facing vendor baseline snapshot after a green runtime contour',
)]
final class VendorReleaseBaselineCommand extends Command
{
    public function __construct(
        private readonly VendorReleaseBaselineReaderInterface $releaseBaselineReader,
        private readonly CommandJsonArtifactWriter $commandJsonArtifactWriter,
        private readonly CommandResultEmitter $commandResultEmitter,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();
        $this
            ->addOption('tenantId', null, InputOption::VALUE_REQUIRED, 'Tenant ID')
            ->addOption('vendorId', null, InputOption::VALUE_REQUIRED, 'Vendor ID')
            ->addOption('from', null, InputOption::VALUE_OPTIONAL, 'Statement period start')
            ->addOption('to', null, InputOption::VALUE_OPTIONAL, 'Statement period end')
            ->addOption('currency', null, InputOption::VALUE_OPTIONAL, 'Currency', 'USD')
            ->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Output format: text|json', 'text')
            ->addOption('write', null, InputOption::VALUE_NONE, 'Write snapshot JSON to build/release')
            ->addOption('output', null, InputOption::VALUE_OPTIONAL, 'Custom output path');
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $runtimeInput = VendorRuntimeWindowInput::fromInput($input);

        if (!$runtimeInput->hasRequiredScope()) {
            $this->commandResultEmitter->emitError($output, $runtimeInput->format, 'invalid', 'Both --tenantId and --vendorId are required.', [
                'tenantId' => $runtimeInput->tenantId,
                'vendorId' => $runtimeInput->vendorId,
            ]);

            return Command::FAILURE;
        }

        try {
            $view = $this->releaseBaselineReader->build(
                tenantId: $runtimeInput->tenantId,
                vendorId: $runtimeInput->vendorId,
                from: $runtimeInput->from,
                to: $runtimeInput->to,
                currency: $runtimeInput->currency,
            )->toArray();
        } catch (Throwable $throwable) {
            $this->commandResultEmitter->emitThrowableError(
                $output,
                $runtimeInput->format,
                'failed',
                'Failed to build vendor release baseline',
                $throwable,
                [
                    'tenantId' => $runtimeInput->tenantId,
                    'vendorId' => $runtimeInput->vendorId,
                    'from' => $runtimeInput->from,
                    'to' => $runtimeInput->to,
                    'currency' => $runtimeInput->currency,
                ],
            );

            return Command::FAILURE;
        }

        try {
            $writtenPath = $this->commandJsonArtifactWriter->writeIfRequested(
                (bool) $input->getOption('write'),
                $input->getOption('output'),
                dirname(__DIR__, 2) . '/build/release/vendor-release-baseline.json',
                $view,
            );
        } catch (CommandIoException $exception) {
            $this->commandResultEmitter->emitError($output, $runtimeInput->format, 'failed', $exception->getMessage(), [
                'tenantId' => $runtimeInput->tenantId,
                'vendorId' => $runtimeInput->vendorId,
                'from' => $runtimeInput->from,
                'to' => $runtimeInput->to,
                'currency' => $runtimeInput->currency,
            ]);

            return Command::FAILURE;
        }

        if (CommandOutputFormat::isJson($runtimeInput->format)) {
            return $this->commandResultEmitter->emitJson($output, $view)
                ? Command::SUCCESS
                : Command::FAILURE;
        }

        $output->writeln(sprintf(
            'tenantId=%s vendorId=%s status=%s',
            $runtimeInput->tenantId,
            $runtimeInput->vendorId,
            $view['status'],
        ));
        $output->writeln(sprintf('issues=%d', count($view['issues'])));

        if (null !== $writtenPath) {
            $output->writeln(sprintf('written=%s', $writtenPath));
        }

        return Command::SUCCESS;
    }
}
