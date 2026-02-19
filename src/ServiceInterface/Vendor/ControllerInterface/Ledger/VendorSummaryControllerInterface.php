<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\ControllerInterface\Ledger;

interface VendorSummaryControllerInterface
{

    public function __construct(private readonly LedgerEntryRepository $repo);

    public function summary(string $vendorId, Request $r): JsonResponse;
}
