<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Payout;

interface VendorSettlementCalculatorInterface
{

    public function __construct(private readonly LedgerEntryRepositoryInterface $ledger);

    public function netForPeriod(string $tenantId, string $vendorId, string $from, string $to, string $currency): float;
}
