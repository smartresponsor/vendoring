<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ControllerInterface\Vendor\Command;

interface SendVendorStatementsCommandInterface
{

    public function __construct(private readonly VendorStatementService $svc, private readonly StatementExporterPDF $pdf, private readonly StatementMailerService $mailer);

    public function execute(InputInterface $input, OutputInterface $output): int;
}
