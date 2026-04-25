<?php

declare(strict_types=1);

namespace App\Vendoring\Repository\Ledger;

use App\Vendoring\DTO\Ledger\LedgerAccountSumCriteriaDTO;
use App\Vendoring\Entity\Ledger\LedgerEntry;
use App\Vendoring\RepositoryInterface\Ledger\LedgerEntryRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class LedgerEntryRepository implements LedgerEntryRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function insert(LedgerEntry $entry): void
    {
        $this->entityManager->persist($entry);
        $this->entityManager->flush();
    }

    /**
     * @return list<LedgerEntry>
     */
    public function listByRef(string $tenantId, string $referenceType, string $referenceId, ?string $vendorId = null): array
    {
        $criteria = [
            'tenantId' => $tenantId,
            'referenceType' => $referenceType,
            'referenceId' => $referenceId,
        ];

        if (null !== $vendorId) {
            $criteria['vendorId'] = $vendorId;
        }

        /** @var list<LedgerEntry> $entries */
        $entries = $this->entityManager->getRepository(LedgerEntry::class)->findBy($criteria, ['createdAt' => 'ASC', 'id' => 'ASC']);

        return $entries;
    }

    public function sumByAccount(LedgerAccountSumCriteriaDTO $criteria): float
    {
        $entries = $this->matchingEntries($criteria);
        $total = 0.0;

        foreach ($entries as $entry) {
            if ($entry->debitAccount === $criteria->accountCode) {
                $total += $entry->amount;
            }

            if ($entry->creditAccount === $criteria->accountCode) {
                $total -= $entry->amount;
            }
        }

        return $total;
    }

    /**
     * @return list<object{currency:string,balanceCents:int}>
     */
    public function balancesForVendor(string $vendorId): array
    {
        /** @var list<LedgerEntry> $entries */
        $entries = $this->entityManager->getRepository(LedgerEntry::class)->findBy(['vendorId' => $vendorId], ['createdAt' => 'ASC', 'id' => 'ASC']);
        $balances = [];

        foreach ($entries as $entry) {
            $currency = strtoupper($entry->currency);
            $balances[$currency] ??= 0;
            $balances[$currency] += (int) round($entry->amount * 100);
        }

        $result = [];
        foreach ($balances as $currency => $balanceCents) {
            $result[] = (object) [
                'currency' => $currency,
                'balanceCents' => $balanceCents,
            ];
        }

        return $result;
    }

    /**
     * @return list<LedgerEntry>
     */
    private function matchingEntries(LedgerAccountSumCriteriaDTO $criteria): array
    {
        $criteriaMap = ['tenantId' => $criteria->tenantId];

        if (null !== $criteria->currency) {
            $criteriaMap['currency'] = $criteria->currency;
        }

        if (null !== $criteria->vendorId) {
            $criteriaMap['vendorId'] = $criteria->vendorId;
        }

        /** @var list<LedgerEntry> $entries */
        $entries = $this->entityManager->getRepository(LedgerEntry::class)->findBy($criteriaMap, ['createdAt' => 'ASC', 'id' => 'ASC']);

        return array_values(array_filter(
            $entries,
            static function (LedgerEntry $entry) use ($criteria): bool {
                if (null !== $criteria->from && $entry->createdAt < $criteria->from) {
                    return false;
                }

                if (null !== $criteria->to && $entry->createdAt > $criteria->to) {
                    return false;
                }

                return $entry->debitAccount === $criteria->accountCode || $entry->creditAccount === $criteria->accountCode;
            },
        ));
    }
}
