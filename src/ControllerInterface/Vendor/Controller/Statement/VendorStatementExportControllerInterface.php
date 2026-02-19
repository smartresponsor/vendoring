<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ControllerInterface\Vendor\Controller\Statement;

interface VendorStatementExportControllerInterface
{

    public function __construct(private readonly VendorStatementService $svc, private readonly StatementExporterPDF $pdf);

    public function export(string $vendorId, Request $r): JsonResponse;
}
