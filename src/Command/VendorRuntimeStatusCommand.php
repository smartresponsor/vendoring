<?php

declare(strict_types=1);

namespace App\Command;

use App\Command\Support\CommandIoException;
use App\Command\Support\CommandJsonArtifactWriter;
use App\Command\Support\CommandJsonArtifactWriterInterface;
use App\Command\Support\CommandOutputFormat;
use App\Command\Support\CommandResultEmitter;
use App\Command\Support\CommandResultEmitterInterface;
use App\Command\Support\VendorRuntimeWindowInput;
use App\ServiceInterface\Ops\VendorRuntimeStatusViewBuilderInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

#[AsCommand(
    name: 'app:vendor:runtime-status',
    description: 'Render vendor runtime status across ownership, finance, statement delivery, and integrations',
)]
/**
 * @noinspection DuplicatedCode
 */
final class VendorRuntimeStatusCommand extends Command
{
    private readonly VendorRuntimeStatusViewBuilderInterface $runtimeStatusViewBuilder;
    private readonly CommandJsonArtifactWriterInterface $commandJsonArtifactWriter;
    private readonly CommandResultEmitterInterface $commandResultEmitter;

    public function __construct(
        VendorRuntimeStatusViewBuilderInterface $runtimeStatusViewBuilder,
        ?CommandJsonArtifactWriterInterface $commandJsonArtifactWriter = null,
        ?CommandResultEmitterInterface $commandResultEmitter = null,
    ) {
        $this->runtimeStatusViewBuilder = $runtimeStatusViewBuilder;
        $this->commandJsonArtifactWriter = $commandJsonArtifactWriter ?? self::defaultCommandJsonArtifactWriter();
        $this->commandResultEmitter = $commandResultEmitter ?? self::defaultCommandResultEmitter();
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
            ->addOption('write', null, InputOption::VALUE_NONE, 'Write runtime status JSON to build/release/runtime-status.json')
            ->addOption('output', null, InputOption::VALUE_OPTIONAL, 'Custom runtime status output path');
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
            $view = $this->runtimeStatusViewBuilder->build(
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
                'Failed to build runtime status',
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
                dirname(__DIR__, 2) . '/build/release/runtime-status.json',
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

        $surfaceStatus = $view['surfaceStatus'];
        $output->writeln(sprintf('tenantId=%s vendorId=%s currency=%s', $runtimeInput->tenantId, $runtimeInput->vendorId, $runtimeInput->currency));
        $output->writeln(sprintf(
            'ownership=%s finance=%s statementDelivery=%s externalIntegration=%s',
            $surfaceStatus['ownership'] ? 'ready' : 'missing',
            $surfaceStatus['finance'] ? 'ready' : 'missing',
            $surfaceStatus['statementDelivery'] ? 'ready' : 'missing',
            $surfaceStatus['externalIntegration'] ? 'ready' : 'missing',
        ));

        if (null !== $writtenPath) {
            $output->writeln(sprintf('written=%s', $writtenPath));
        }

        return Command::SUCCESS;
    }

    private static function defaultCommandJsonArtifactWriter(): CommandJsonArtifactWriterInterface
    {
        $encoder = new \App\Command\Support\CommandJsonEncoder();

        return new CommandJsonArtifactWriter(new \App\Command\Support\CommandJsonFileWriter($encoder));
    }

    private static function defaultCommandResultEmitter(): CommandResultEmitterInterface
    {
        return new CommandResultEmitter(new \App\Command\Support\CommandJsonEncoder());
    }
}
