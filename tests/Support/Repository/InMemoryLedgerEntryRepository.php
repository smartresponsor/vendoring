<?php

declare(strict_types=1);

namespace App\Tests\Support\Repository;

use App\DTO\Ledger\LedgerAccountSumCriteriaDTO;
use App\Entity\Ledger\LedgerEntry;
use App\RepositoryInterface\Ledger\LedgerEntryRepositoryInterface;

final class InMemoryLedgerEntryRepository implements LedgerEntryRepositoryInterface
{
    /** @var list<LedgerEntry> */
    private array $entries = [];

    public function insert(LedgerEntry $entry): void
    {
        $this->entries[] = $entry;
    }

    public function listByRef(string $tenantId, string $referenceType, string $referenceId, ?string $vendorId = null): array
    {
        return array_values(array_filter(
            $this->entries,
            static function (LedgerEntry $entry) use ($tenantId, $referenceType, $referenceId, $vendorId): bool {
                if ($entry->tenantId !== $tenantId) {
                    return false;
                }

                if ($entry->referenceType !== $referenceType || $entry->referenceId !== $referenceId) {
                    return false;
                }

                if (null !== $vendorId && $entry->vendorId !== $vendorId) {
                    return false;
                }

                return true;
            },
        ));
    }

    public function sumByAccount(LedgerAccountSumCriteriaDTO $criteria): float
    {
        $sum = 0.0;

        foreach ($this->entries as $entry) {
            if ($entry->tenantId !== $criteria->tenantId) {
                continue;
            }

            if (null !== $criteria->vendorId && $entry->vendorId !== $criteria->vendorId) {
                continue;
            }

            if (null !== $criteria->currency && $entry->currency !== $criteria->currency) {
                continue;
            }

            if (null !== $criteria->from && $entry->createdAt < $criteria->from) {
                continue;
            }

            if (null !== $criteria->to && $entry->createdAt > $criteria->to) {
                continue;
            }

            if ($entry->debitAccount === $criteria->accountCode) {
                $sum += $entry->amount;
            }

            if ($entry->creditAccount === $criteria->accountCode) {
                $sum -= $entry->amount;
            }
        }

        return $sum;
    }

    public function balancesForVendor(string $vendorId): array
    {
        $balances = [];

        foreach ($this->entries as $entry) {
            if ($entry->vendorId !== $vendorId) {
                continue;
            }

            $balances[$entry->currency] ??= 0;
            $balances[$entry->currency] += (int) round($entry->amount * 100);
        }

        return array_map(
            static fn(string $currency, int $balanceCents): object => (object) [
                'currency' => $currency,
                'balanceCents' => $balanceCents,
            ],
            array_keys($balances),
            array_values($balances),
        );
    }

    /**
     * @return list<LedgerEntry>
     */
    public function all(): array
    {
        return $this->entries;
    }
}
