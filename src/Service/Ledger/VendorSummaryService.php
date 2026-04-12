<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Ledger;

use Doctrine\DBAL\Exception;
use App\DTO\Ledger\LedgerAccountSumCriteriaDTO;
use App\RepositoryInterface\Ledger\LedgerEntryRepositoryInterface;
use App\ServiceInterface\Ledger\VendorSummaryServiceInterface;

final class VendorSummaryService implements VendorSummaryServiceInterface
{
    private const array ACCOUNTS = ['REVENUE', 'REFUNDS_PAYABLE', 'VENDOR_PAYABLE', 'CASH', 'payout_fee'];

    public function __construct(private readonly LedgerEntryRepositoryInterface $ledgerEntries) {}

    /** @throws Exception */
    public function build(string $tenantId, string $vendorId, string $from, string $to, string $currency): array
    {
        $balances = [];
        foreach (self::ACCOUNTS as $account) {
            $balances[$account] = $this->ledgerEntries->sumByAccount(new LedgerAccountSumCriteriaDTO(
                tenantId: $tenantId,
                accountCode: $account,
                from: $this->normalizeBoundary($from, false),
                to: $this->normalizeBoundary($to, true),
                vendorId: $vendorId,
                currency: '' !== $currency ? $currency : null,
            ));
        }

        return [
            'vendorId' => $vendorId,
            'from' => $from,
            'to' => $to,
            'currency' => $currency,
            'balances' => $balances,
        ];
    }

    private function normalizeBoundary(string $value, bool $endOfDay): ?string
    {
        if ('' === trim($value)) {
            return null;
        }

        $timestamp = strtotime($value);
        if (false === $timestamp) {
            return $value;
        }

        return date($endOfDay ? 'Y-m-d 23:59:59' : 'Y-m-d 00:00:00', $timestamp);
    }
}
