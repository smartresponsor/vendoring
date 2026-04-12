<?php

declare(strict_types=1);

namespace App\Command;

use App\Command\Support\CommandOutputFormat;
use App\Command\Support\CommandResultEmitter;
use App\Command\Support\CommandResultEmitterInterface;
use App\DTO\Statement\VendorStatementRecipientDTO;
use App\DTO\Statement\VendorStatementRequestDTO;
use App\ServiceInterface\Statement\StatementExporterPDFInterface;
use App\ServiceInterface\Statement\VendorStatementMailerServiceInterface;
use App\ServiceInterface\Statement\VendorStatementRecipientProviderInterface;
use App\ServiceInterface\Statement\VendorStatementServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

#[AsCommand(name: 'finance:send-vendor-statements', description: 'Send monthly vendor statements')]
final class SendVendorStatementsCommand extends Command
{
    private readonly VendorStatementServiceInterface $svc;
    private readonly StatementExporterPDFInterface $pdf;
    private readonly VendorStatementMailerServiceInterface $mailer;
    private readonly VendorStatementRecipientProviderInterface $recipientProvider;
    private readonly CommandResultEmitterInterface $commandResultEmitter;

    public function __construct(
        VendorStatementServiceInterface $svc,
        StatementExporterPDFInterface $pdf,
        VendorStatementMailerServiceInterface $mailer,
        VendorStatementRecipientProviderInterface $recipientProvider,
        ?CommandResultEmitterInterface $commandResultEmitter = null,
    ) {
        $this->svc = $svc;
        $this->pdf = $pdf;
        $this->mailer = $mailer;
        $this->recipientProvider = $recipientProvider;
        $this->commandResultEmitter = $commandResultEmitter ?? self::defaultCommandResultEmitter();
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();
        $this
            ->addOption('tenant-id', null, InputOption::VALUE_REQUIRED)
            ->addOption('vendor-id', null, InputOption::VALUE_REQUIRED)
            ->addOption('email', null, InputOption::VALUE_REQUIRED)
            ->addOption('currency', null, InputOption::VALUE_REQUIRED, 'Statement currency', 'USD')
            ->addOption('from', null, InputOption::VALUE_REQUIRED)
            ->addOption('to', null, InputOption::VALUE_REQUIRED)
            ->addOption('period-label', null, InputOption::VALUE_REQUIRED)
            ->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Output format: text|json', 'text');
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $from = $this->resolveDateOption($this->stringOption($input, 'from'), date('Y-m-01'));
        $to = $this->resolveDateOption($this->stringOption($input, 'to'), date('Y-m-t'));
        $period = $this->resolvePeriodLabel($this->stringOption($input, 'period-label'), $from, $to);
        $format = CommandOutputFormat::normalize($input->getOption('format'));

        try {
            $recipients = $this->resolveRecipients($input, $from, $to);
        } catch (Throwable $throwable) {
            $this->commandResultEmitter->emitThrowableError($output, $format, 'failed', 'Failed to resolve statement recipients', $throwable, [
                'period' => $period,
                'from' => $from,
                'to' => $to,
            ]);

            return Command::FAILURE;
        }

        if ([] === $recipients) {
            if (CommandOutputFormat::isJson($format)) {
                return $this->commandResultEmitter->emitJson($output, [
                    'status' => 'no_recipients',
                    'period' => $period,
                    'from' => $from,
                    'to' => $to,
                    'recipients' => [],
                    'results' => [],
                ]) ? Command::SUCCESS : Command::FAILURE;
            }

            $output->writeln(sprintf('NO_RECIPIENTS period=%s from=%s to=%s', $period, $from, $to));

            return Command::SUCCESS;
        }

        $results = [];
        $failures = 0;

        foreach ($recipients as $recipient) {
            try {
                $dto = new VendorStatementRequestDTO($recipient->tenantId, $recipient->vendorId, $from, $to, $recipient->currency);
                $data = $this->svc->build($dto);
                $pdfPath = $this->pdf->export($dto, $data);
                $result = $this->mailer->send($recipient->tenantId, $recipient->vendorId, $recipient->email, $pdfPath, $period);
            } catch (Throwable $throwable) {
                $result = [
                    'ok' => false,
                    'message' => 'statement_generation_failed',
                    'tenantId' => $recipient->tenantId,
                    'vendorId' => $recipient->vendorId,
                    'email' => $recipient->email,
                    'pdfPath' => '',
                    'periodLabel' => $period,
                    'attached' => false,
                    'retryable' => false,
                    'timeoutSeconds' => 0,
                    'maxAttempts' => 0,
                    'attemptCount' => 0,
                    'failureMode' => 'hard',
                    'circuitState' => 'unknown',
                    'errorClass' => $throwable::class,
                    'errorMessage' => $throwable->getMessage(),
                ];
            }

            if (true !== ($result['ok'] ?? false)) {
                ++$failures;
            }

            $results[] = $result;

            if (!CommandOutputFormat::isJson($format)) {
                $output->writeln(sprintf(
                    '[%s/%s] %s email=%s period=%s currency=%s pdf=%s attached=%s message=%s',
                    $recipient->tenantId,
                    $recipient->vendorId,
                    ($result['ok'] ?? false) ? 'SENT' : 'FAIL',
                    $result['email'] ?? $recipient->email,
                    $result['periodLabel'] ?? $period,
                    $recipient->currency,
                    $result['pdfPath'] ?? '',
                    $result['attached'] ?? false ? 'yes' : 'no',
                    $result['message'] ?? 'statement_mail_send_failed',
                ));
            }
        }

        if (CommandOutputFormat::isJson($format)) {
            if (!$this->commandResultEmitter->emitJson($output, [
                'status' => 0 === $failures ? 'completed' : 'completed_with_failures',
                'period' => $period,
                'from' => $from,
                'to' => $to,
                'recipientCount' => count($recipients),
                'failureCount' => $failures,
                'results' => $results,
            ])) {
                return Command::FAILURE;
            }
        }

        return 0 === $failures ? Command::SUCCESS : Command::FAILURE;
    }

    /** @return list<VendorStatementRecipientDTO> */
    private function resolveRecipients(InputInterface $input, string $from, string $to): array
    {
        $tenantId = trim($this->stringOption($input, 'tenant-id'));
        $vendorId = trim($this->stringOption($input, 'vendor-id'));
        $email = trim($this->stringOption($input, 'email'));
        $currency = strtoupper(trim($this->stringOption($input, 'currency')));

        if ('' !== $tenantId || '' !== $vendorId || '' !== $email) {
            if ('' === $tenantId || '' === $vendorId || '' === $email) {
                return [];
            }

            return [new VendorStatementRecipientDTO($tenantId, $vendorId, $email, '' !== $currency ? $currency : 'USD')];
        }

        return $this->recipientProvider->forPeriod($from, $to);
    }

    private function resolveDateOption(string $value, string $fallback): string
    {
        return '' !== trim($value) ? $value : $fallback;
    }

    private function resolvePeriodLabel(string $periodLabel, string $from, string $to): string
    {
        if ('' !== trim($periodLabel)) {
            return $periodLabel;
        }

        $fromMonth = date('Y-m', $this->safeTimestamp($from));
        $toMonth = date('Y-m', $this->safeTimestamp($to));

        if ($fromMonth === $toMonth) {
            return date('F Y', $this->safeTimestamp($from));
        }

        return sprintf('%s to %s', $from, $to);
    }

    private function stringOption(InputInterface $input, string $name): string
    {
        $value = $input->getOption($name);

        return is_scalar($value) ? (string) $value : '';
    }

    private function safeTimestamp(string $value): int
    {
        $timestamp = strtotime($value);

        return false === $timestamp ? time() : $timestamp;
    }

    private static function defaultCommandResultEmitter(): CommandResultEmitterInterface
    {
        return new CommandResultEmitter(new \App\Command\Support\CommandJsonEncoder());
    }
}
