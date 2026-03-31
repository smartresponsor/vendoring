<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Ledger;

use App\RepositoryInterface\Ledger\LedgerEntryRepositoryInterface;
use App\ServiceInterface\Ledger\VendorSummaryServiceInterface;

final class VendorSummaryService implements VendorSummaryServiceInterface
{
    private const ACCOUNTS = ['REVENUE', 'REFUNDS_PAYABLE', 'payout_fee', 'VENDOR_PAYABLE', 'CASH'];

    public function __construct(private readonly LedgerEntryRepositoryInterface $ledgerEntries)
    {
    }

    public function build(string $tenantId, string $vendorId, string $from, string $to, string $currency): array
    {
        $fromDate = $this->normalizeDateOrNull($from);
        $toDate = $this->normalizeDateOrNull($to);
        $currencyFilter = '' !== trim($currency) ? $currency : null;

        $balances = [];
        foreach (self::ACCOUNTS as $account) {
            $balances[$account] = $this->ledgerEntries->sumByAccount(
                $tenantId,
                $account,
                $fromDate,
                $toDate,
                $vendorId,
                $currencyFilter,
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

    private function normalizeDateOrNull(string $value): ?string
    {
        $trimmed = trim($value);

        if ('' === $trimmed) {
            return null;
        }

        return (new \DateTimeImmutable($trimmed))->format('Y-m-d');
    }
}
