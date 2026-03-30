<?php

declare(strict_types=1);

namespace App\Tests\Support\Repository;

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
            }
        ));
    }

    public function sumByAccount(string $tenantId, string $accountCode, ?string $from = null, ?string $to = null, ?string $vendorId = null, ?string $currency = null): float
    {
        $sum = 0.0;
        $fromBound = $this->lowerBound($from);
        $toBound = $this->upperBound($to);

        foreach ($this->entries as $entry) {
            if ($entry->tenantId !== $tenantId) {
                continue;
            }

            if (null !== $vendorId && $entry->vendorId !== $vendorId) {
                continue;
            }

            if (null !== $currency && $entry->currency !== $currency) {
                continue;
            }

            $entryCreatedAt = new \DateTimeImmutable($entry->createdAt);

            if (null !== $fromBound && $entryCreatedAt < $fromBound) {
                continue;
            }

            if (null !== $toBound && $entryCreatedAt > $toBound) {
                continue;
            }

            if ($entry->debitAccount === $accountCode) {
                $sum += $entry->amount;
            }

            if ($entry->creditAccount === $accountCode) {
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
            static fn (string $currency, int $balanceCents): object => (object) [
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

    private function lowerBound(?string $value): ?\DateTimeImmutable
    {
        if (null === $value || '' === trim($value)) {
            return null;
        }

        $trimmed = trim($value);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $trimmed)) {
            return new \DateTimeImmutable($trimmed.' 00:00:00');
        }

        return new \DateTimeImmutable($trimmed);
    }

    private function upperBound(?string $value): ?\DateTimeImmutable
    {
        if (null === $value || '' === trim($value)) {
            return null;
        }

        $trimmed = trim($value);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $trimmed)) {
            return new \DateTimeImmutable($trimmed.' 23:59:59');
        }

        return new \DateTimeImmutable($trimmed);
    }
}
