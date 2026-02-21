<?php

declare(strict_types=1);

namespace App\Command\Vendor;

use App\DTO\Vendor\Statement\VendorStatementRequestDTO;
use App\Service\Vendor\Statement\StatementExporterPDF;
use App\Service\Vendor\Statement\StatementMailerService;
use App\Service\Vendor\Statement\VendorStatementService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'finance:send-vendor-statements', description: 'Send monthly vendor statements')]
final class SendVendorStatementsCommand extends Command
{
    public function __construct(
        private readonly VendorStatementService $svc,
        private readonly StatementExporterPDF $pdf,
        private readonly StatementMailerService $mailer,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // In real system: fetch vendors & emails from repository; here mock one.
        $vendors = [['tenantId' => 't', 'vendorId' => 'v', 'email' => 'vendor@example.com', 'currency' => 'USD']];
        $from = date('Y-m-01');
        $to = date('Y-m-t');
        $period = date('F Y');
        foreach ($vendors as $v) {
            $dto = new VendorStatementRequestDTO($v['tenantId'], $v['vendorId'], $from, $to, $v['currency']);
            $data = $this->svc->build($dto);
            $pdfPath = $this->pdf->export($dto, $data, null);
            $res = $this->mailer->send($v['tenantId'], $v['vendorId'], $v['email'], $pdfPath, $period);
            $output->writeln(sprintf('[%s] %s: %s', $v['vendorId'], $res['ok'] ? 'SENT' : 'FAIL', $res['message']));
        }

        return Command::SUCCESS;
    }
}
