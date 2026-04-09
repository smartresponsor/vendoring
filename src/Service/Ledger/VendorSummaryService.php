<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Ledger;

use App\RepositoryInterface\Ledger\LedgerEntryRepositoryInterface;
use App\ServiceInterface\Ledger\VendorSummaryServiceInterface;

/**
 * Application service for vendor summary operations.
 */
final class VendorSummaryService implements VendorSummaryServiceInterface
{
    private const array ACCOUNTS = ['REVENUE', 'REFUNDS_PAYABLE', 'VENDOR_PAYABLE', 'CASH'];

    public function __construct(private readonly LedgerEntryRepositoryInterface $ledgerEntries)
    {
    }

    /**
     * Builds the requested read model.
     */
    public function build(string $tenantId, string $vendorId, string $from, string $to, string $currency): array
    {
        $balances = [];
        foreach (self::ACCOUNTS as $account) {
            $balances[$account] = $this->ledgerEntries->sumByAccount(
                $tenantId,
                $account,
                '' !== $from ? $from : null,
                '' !== $to ? $to : null,
                $vendorId,
                '' !== $currency ? $currency : null,
            );
        }

        return [
            'vendorId' => $vendorId,
            'from' => $from,
            'to' => $to,
            'currency' => $currency,
            'balances' => $balances,
        ];
    }
}
