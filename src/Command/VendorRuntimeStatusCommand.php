<?php

declare(strict_types=1);

namespace App\Vendoring\Command;

use App\Vendoring\Service\Command\VendorCommandJsonArtifactWriterService;
use App\Vendoring\Service\Command\VendorCommandJsonEncoderService;
use App\Vendoring\Service\Command\VendorCommandJsonFileWriterService;
use App\Vendoring\Service\Command\VendorCommandResultEmitterService;
use App\Vendoring\Exception\Command\VendorCommandIoException;
use App\Vendoring\ServiceInterface\Command\VendorCommandJsonArtifactWriterServiceInterface;
use App\Vendoring\Enum\Command\VendorCommandOutputFormatEnum;
use App\Vendoring\ServiceInterface\Command\VendorCommandResultEmitterServiceInterface;
use App\Vendoring\DTO\Command\VendorRuntimeWindowInputDTO;
use App\Vendoring\ServiceInterface\Ops\VendorRuntimeStatusProjectionBuilderServiceInterface;
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
    private readonly VendorRuntimeStatusProjectionBuilderServiceInterface $runtimeStatusProjectionBuilder;
    private readonly VendorCommandJsonArtifactWriterServiceInterface $commandJsonArtifactWriter;
    private readonly VendorCommandResultEmitterServiceInterface $commandResultEmitter;

    public function __construct(
        VendorRuntimeStatusProjectionBuilderServiceInterface $runtimeStatusProjectionBuilder,
        ?VendorCommandJsonArtifactWriterServiceInterface $commandJsonArtifactWriter = null,
        ?VendorCommandResultEmitterServiceInterface $commandResultEmitter = null,
    ) {
        $this->runtimeStatusProjectionBuilder = $runtimeStatusProjectionBuilder;
        $this->commandJsonArtifactWriter = $commandJsonArtifactWriter ?? self::defaultVendorCommandJsonArtifactWriterService();
        $this->commandResultEmitter = $commandResultEmitter ?? self::defaultVendorCommandResultEmitterService();
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
        $runtimeInput = VendorRuntimeWindowInputDTO::fromInput($input);

        if (!$runtimeInput->hasRequiredScope()) {
            $this->commandResultEmitter->emitError($output, $runtimeInput->format, 'invalid', 'Both --tenantId and --vendorId are required.', [
                'tenantId' => $runtimeInput->tenantId,
                'vendorId' => $runtimeInput->vendorId,
            ]);

            return Command::FAILURE;
        }

        try {
            $projection = $this->runtimeStatusProjectionBuilder->build(
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
                $projection,
            );
        } catch (VendorCommandIoException $exception) {
            $this->commandResultEmitter->emitError($output, $runtimeInput->format, 'failed', $exception->getMessage(), [
                'tenantId' => $runtimeInput->tenantId,
                'vendorId' => $runtimeInput->vendorId,
                'from' => $runtimeInput->from,
                'to' => $runtimeInput->to,
                'currency' => $runtimeInput->currency,
            ]);

            return Command::FAILURE;
        }

        if (VendorCommandOutputFormatEnum::isJson($runtimeInput->format)) {
            return $this->commandResultEmitter->emitJson($output, $projection)
                ? Command::SUCCESS
                : Command::FAILURE;
        }

        $surfaceStatus = $projection['surfaceStatus'];
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

    private static function defaultVendorCommandJsonArtifactWriterService(): VendorCommandJsonArtifactWriterServiceInterface
    {
        $encoder = new VendorCommandJsonEncoderService();

        return new VendorCommandJsonArtifactWriterService(new VendorCommandJsonFileWriterService($encoder));
    }

    private static function defaultVendorCommandResultEmitterService(): VendorCommandResultEmitterServiceInterface
    {
        return new VendorCommandResultEmitterService(new VendorCommandJsonEncoderService());
    }
}
