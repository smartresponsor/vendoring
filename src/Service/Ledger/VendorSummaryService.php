<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\Service\Ledger;

use App\Vendoring\DTO\Ledger\VendorLedgerAccountSumCriteriaDTO;
use App\Vendoring\RepositoryInterface\Vendor\VendorLedgerRepositoryInterface;
use App\Vendoring\ServiceInterface\Ledger\VendorSummaryServiceInterface;
use Doctrine\DBAL\Exception;

final class VendorSummaryService implements VendorSummaryServiceInterface
{
    private const array ACCOUNTS = ['REVENUE', 'REFUNDS_PAYABLE', 'VENDOR_PAYABLE', 'CASH', 'payout_fee'];

    public function __construct(private readonly VendorLedgerRepositoryInterface $ledgerEntries)
    {
    }

    /** @throws Exception */
    public function build(string $tenantId, string $vendorId, string $from, string $to, string $currency): array
    {
        $balances = [];
        foreach (self::ACCOUNTS as $account) {
            $balances[$account] = $this->ledgerEntries->sumByAccount(new VendorLedgerAccountSumCriteriaDTO(
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
