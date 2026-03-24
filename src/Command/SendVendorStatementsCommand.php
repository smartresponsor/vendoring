<?php

declare(strict_types=1);

namespace App\Command;

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

#[AsCommand(name: 'finance:send-vendor-statements', description: 'Send monthly vendor statements')]
final class SendVendorStatementsCommand extends Command
{
    public function __construct(
        private readonly VendorStatementServiceInterface $svc,
        private readonly StatementExporterPDFInterface $pdf,
        private readonly VendorStatementMailerServiceInterface $mailer,
        private readonly VendorStatementRecipientProviderInterface $recipientProvider,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('tenant-id', null, InputOption::VALUE_REQUIRED)
            ->addOption('vendor-id', null, InputOption::VALUE_REQUIRED)
            ->addOption('email', null, InputOption::VALUE_REQUIRED)
            ->addOption('currency', null, InputOption::VALUE_REQUIRED, 'Statement currency', 'USD')
            ->addOption('from', null, InputOption::VALUE_REQUIRED)
            ->addOption('to', null, InputOption::VALUE_REQUIRED)
            ->addOption('period-label', null, InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $from = $this->resolveDateOption((string) $input->getOption('from'), date('Y-m-01'));
        $to = $this->resolveDateOption((string) $input->getOption('to'), date('Y-m-t'));
        $period = $this->resolvePeriodLabel((string) $input->getOption('period-label'), $from, $to);

        $recipients = $this->resolveRecipients($input, $from, $to);
        if ([] === $recipients) {
            $output->writeln(sprintf('NO_RECIPIENTS period=%s from=%s to=%s', $period, $from, $to));

            return Command::SUCCESS;
        }

        foreach ($recipients as $recipient) {
            $dto = new VendorStatementRequestDTO($recipient->tenantId, $recipient->vendorId, $from, $to, $recipient->currency);
            $data = $this->svc->build($dto);
            $pdfPath = $this->pdf->export($dto, $data, null);
            $res = $this->mailer->send($recipient->tenantId, $recipient->vendorId, $recipient->email, $pdfPath, $period);
            $output->writeln(sprintf(
                '[%s/%s] %s email=%s period=%s currency=%s pdf=%s attached=%s message=%s',
                $recipient->tenantId,
                $recipient->vendorId,
                $res['ok'] ? 'SENT' : 'FAIL',
                $res['email'] ?? $recipient->email,
                $res['periodLabel'] ?? $period,
                $dto->currency,
                $res['pdfPath'] ?? $pdfPath,
                ($res['attached'] ?? false) ? 'yes' : 'no',
                $res['message']
            ));
        }

        return Command::SUCCESS;
    }

    /** @return list<VendorStatementRecipientDTO> */
    private function resolveRecipients(InputInterface $input, string $from, string $to): array
    {
        $tenantId = trim((string) $input->getOption('tenant-id'));
        $vendorId = trim((string) $input->getOption('vendor-id'));
        $email = trim((string) $input->getOption('email'));
        $currency = strtoupper(trim((string) $input->getOption('currency')));

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

        $fromMonth = date('Y-m', strtotime($from));
        $toMonth = date('Y-m', strtotime($to));

        if ($fromMonth === $toMonth) {
            return date('F Y', strtotime($from));
        }

        return sprintf('%s to %s', $from, $to);
    }
}
