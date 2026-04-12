<?php

declare(strict_types=1);

namespace App\Command;

use App\Command\Support\CommandJsonEncoder;
use App\Command\Support\CommandOutputFormat;
use App\Command\Support\CommandResultEmitter;
use App\Command\Support\CommandResultEmitterInterface;
use App\ServiceInterface\Payout\VendorPayoutServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(name: 'app:vendor:payout:process', description: 'Process an existing vendor payout.')]
final class VendorPayoutProcessCommand extends Command
{
    private readonly VendorPayoutServiceInterface $payouts;
    private readonly CommandResultEmitterInterface $commandResultEmitter;

    public function __construct(
        VendorPayoutServiceInterface $payouts,
        ?CommandResultEmitterInterface $commandResultEmitter = null,
    ) {
        $this->payouts = $payouts;
        $this->commandResultEmitter = $commandResultEmitter ?? self::defaultCommandResultEmitter();
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();
        $this
            ->addArgument('payoutId', InputArgument::REQUIRED)
            ->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Output format: text|json', 'text')
            ->addOption('json', null, InputOption::VALUE_NONE, 'Deprecated alias for --format=json.');
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $argument = $input->getArgument('payoutId');
        $payoutId = is_scalar($argument) ? trim((string) $argument) : '';
        $format = true === $input->getOption('json')
            ? CommandOutputFormat::JSON
            : CommandOutputFormat::normalize($input->getOption('format'));

        if ('' === $payoutId) {
            $this->commandResultEmitter->emitError($output, $format, 'invalid', 'Invalid payoutId', [
                'payoutId' => $payoutId,
            ]);

            return Command::FAILURE;
        }

        try {
            $processed = $this->payouts->process($payoutId);
        } catch (Throwable $throwable) {
            $this->commandResultEmitter->emitThrowableError($output, $format, 'failed', 'Failed to process payout', $throwable, [
                'payoutId' => $payoutId,
            ]);

            return Command::FAILURE;
        }

        if (CommandOutputFormat::isJson($format)) {
            return $this->commandResultEmitter->emitJson($output, [
                'payoutId' => $payoutId,
                'processed' => $processed,
                'status' => $processed ? 'processed' : 'rejected',
            ]) ? Command::SUCCESS : Command::FAILURE;
        }

        $io = new SymfonyStyle($input, $output);

        if ($processed) {
            $io->success(sprintf('Processed payout %s.', $payoutId));

            return Command::SUCCESS;
        }

        $io->warning(sprintf('Payout %s was not processed.', $payoutId));

        return Command::SUCCESS;
    }

    private static function defaultCommandResultEmitter(): CommandResultEmitterInterface
    {
        return new CommandResultEmitter(new CommandJsonEncoder());
    }
}
